<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Widgets\MeatHubStatsOverview;
use App\Models\Order;
use App\Models\Producer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_csrf_token_during_logout_clears_the_session_and_redirects_home(): void
    {
        $this->actingAs(User::factory()->create());

        $request = Request::create('/account/logout', 'POST');
        $request->setLaravelSession($this->app['session']->driver());
        $request->session()->put('logout-test', 'present');

        $response = app(ExceptionHandler::class)->render($request, new TokenMismatchException);

        $this->assertSame(route('home'), $response->headers->get('Location'));
        $this->assertFalse($request->session()->has('logout-test'));
        $this->assertGuest();
    }

    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Contact Us')
            ->assertSee('meathub@gmail.com')
            ->assertSee('+63 900 000 0000')
            ->assertDontSee('(demo)')
            ->assertSee('Alcate Victoria');
    }

    public function test_marketplace_shows_product_details_and_category_filters(): void
    {
        $producer = Producer::create([
            'name' => 'San Isidro Farm',
            'location' => 'Bulacan',
            'is_active' => true,
        ]);

        Product::create([
            'name' => 'Pork Belly',
            'producer_id' => $producer->id,
            'category' => 'Pork',
            'price' => 320,
            'stock_kg' => 4,
            'description' => 'Fresh pork belly.',
            'is_available' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pork Belly')
            ->assertSee('San Isidro Farm')
            ->assertSee('Price per KG')
            ->assertSee('Available')
            ->assertSee('Our producers')
            ->assertSee('Bulacan')
            ->assertSee('Add to Cart')
            ->assertSee('Organ Meat');
    }

    public function test_storefront_only_shows_active_producers(): void
    {
        Producer::create(['name' => 'Active Farm', 'is_active' => true]);
        Producer::create(['name' => 'Hidden Farm', 'is_active' => false]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Active Farm')
            ->assertDontSee('Hidden Farm');
    }

    public function test_admin_stats_calculate_revenue_orders_and_low_stock(): void
    {
        foreach ([
            ['total' => 500, 'payment_status' => 'paid'],
            ['total' => 200, 'payment_status' => 'completed'],
            ['total' => 50, 'payment_status' => 'pending'],
            ['total' => 100, 'payment_status' => 'cancelled'],
        ] as $attributes) {
            Order::create([
                'delivery_address' => '123 Market Street, Manila',
                ...$attributes,
            ]);
        }

        Product::create(['name' => 'Low stock', 'price' => 100, 'stock_kg' => 5]);
        Product::create(['name' => 'In stock', 'price' => 100, 'stock_kg' => 6]);

        $method = new \ReflectionMethod(MeatHubStatsOverview::class, 'getStats');
        $method->setAccessible(true);
        $stats = $method->invoke(app(MeatHubStatsOverview::class));

        $this->assertSame('₱700.00', $stats[0]->getValue());
        $this->assertSame('4', $stats[1]->getValue());
        $this->assertSame('1 pending · 2 paid/completed', $stats[1]->getDescription());
        $this->assertSame('1', $stats[2]->getValue());
        $this->assertSame('warning', $stats[2]->getColor());
    }

    public function test_filament_dashboard_and_new_resource_pages_render(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        $this->get('/admin')
            ->assertOk()
            ->assertSee('Total Revenue')
            ->assertSee('Low Stock Products');
        $this->get('/admin/orders')->assertOk();
        $this->get('/admin/producers')->assertOk();
    }

    public function test_marking_an_order_paid_deducts_stock_only_when_not_already_reserved(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        $product = Product::create(['name' => 'Pork Belly', 'price' => 320, 'stock_kg' => 10]);
        $order = Order::create([
            'order_number' => 'MH-00000001',
            'delivery_address' => '123 Market Street, Manila',
            'total' => 640,
            'payment_reference' => 'GCASH-REF-123',
            'payment_status' => 'pending',
            'stock_deducted' => false,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_kg' => 2,
            'unit_price' => 320,
            'subtotal' => 640,
        ]);

        Livewire::test(ListOrders::class)
            ->callTableAction('markPaid', $order);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'stock_deducted' => true,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_kg' => 8]);

        $reservedOrder = Order::create([
            'order_number' => 'MH-00000002',
            'delivery_address' => '456 Market Street, Manila',
            'total' => 320,
            'payment_reference' => 'GCASH-REF-456',
            'payment_status' => 'pending',
            'stock_deducted' => true,
        ]);
        $reservedOrder->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_kg' => 1,
            'unit_price' => 320,
            'subtotal' => 320,
        ]);

        Livewire::test(ListOrders::class)
            ->callTableAction('markPaid', $reservedOrder);

        $this->assertDatabaseHas('orders', ['id' => $reservedOrder->id, 'payment_status' => 'paid']);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_kg' => 8]);
    }

    public function test_cancelling_a_pending_order_releases_reserved_stock(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        $product = Product::create(['name' => 'Beef Ribs', 'price' => 500, 'stock_kg' => 3]);
        $order = Order::create([
            'order_number' => 'MH-00000001',
            'delivery_address' => '123 Market Street, Manila',
            'total' => 500,
            'payment_status' => 'pending',
            'stock_deducted' => true,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_kg' => 2,
            'unit_price' => 500,
            'subtotal' => 500,
        ]);

        Livewire::test(ListOrders::class)
            ->callTableAction('cancel', $order);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'cancelled',
            'stock_deducted' => false,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_kg' => 5]);
    }

    public function test_customer_can_place_a_gcash_order_and_submit_a_payment_reference(): void
    {
        config(['services.gcash.qr_image' => 'images/gcash-qr.png']);
        $customer = User::factory()->create();
        $customer->is_admin = false;
        $customer->save();
        $this->actingAs($customer);

        $producer = Producer::create(['name' => 'San Isidro Farm', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Pork Belly',
            'producer_id' => $producer->id,
            'category' => 'Pork',
            'price' => 320,
            'stock_kg' => 4,
            'is_available' => true,
        ]);

        $response = $this->postJson(route('orders.store'), [
            'delivery_address' => '123 Market Street, Manila',
            'items' => [
                ['product_id' => $product->id, 'quantity_kg' => 2],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('order_number', 'MH-00000001');
        $this->get($response->json('redirect_url'))
            ->assertOk()
            ->assertSee('Pay with GCash')
            ->assertSee('MH-00000001')
            ->assertSee('images/gcash-qr.png');

        $this->assertDatabaseHas('orders', [
            'order_number' => 'MH-00000001',
            'user_id' => $customer->id,
            'total' => '640.00',
            'payment_method' => 'gcash',
            'payment_status' => 'pending',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => 1,
            'product_id' => $product->id,
            'producer_name' => 'San Isidro Farm',
            'quantity_kg' => 2,
            'unit_price' => '320.00',
            'subtotal' => '640.00',
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_kg' => 2]);

        $order = Order::query()->firstOrFail();
        $referenceUrl = URL::temporarySignedRoute(
            'orders.payment-reference',
            now()->addMinutes(10),
            ['orderNumber' => $order->order_number],
        );

        Storage::fake('public');
        $this->post($referenceUrl, [
            'payment_reference' => 'GCASH-REF-123',
            'gcash_receipt' => UploadedFile::fake()->image('receipt.png'),
        ])
            ->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_reference' => 'GCASH-REF-123',
            'payment_status' => 'pending',
        ]);
        $this->assertNotNull($order->fresh()->gcash_receipt);
        $this->assertTrue(Storage::disk('public')->exists($order->fresh()->gcash_receipt));

        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());
        Livewire::test(ListOrders::class)
            ->assertCanRenderTableColumn('gcash_receipt')
            ->callTableAction('view', $order->fresh())
            ->assertSee('/storage/receipts/')
            ->assertSee('GCASH-REF-123');
    }

    public function test_customer_cannot_order_more_than_available_stock(): void
    {
        config(['services.gcash.qr_image' => 'images/gcash-qr.png']);
        $this->actingAs(User::factory()->create());

        $product = Product::create([
            'name' => 'Pork Belly',
            'category' => 'Pork',
            'price' => 320,
            'stock_kg' => 1,
            'is_available' => true,
        ]);

        $this->postJson(route('orders.store'), [
            'delivery_address' => '123 Market Street, Manila',
            'items' => [
                ['product_id' => $product->id, 'quantity_kg' => 2],
            ],
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('items');

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'stock_kg' => 1]);
    }

    public function test_gcash_orders_are_unavailable_until_the_qr_is_configured(): void
    {
        config(['services.gcash.qr_image' => null]);

        $this->get('/')
            ->assertOk()
            ->assertSee('GCash QR not configured');
    }

    public function test_customer_accounts_receive_order_status_notifications(): void
    {
        config(['services.gcash.qr_image' => 'images/gcash-qr.png']);

        $this->post(route('account.register.store'), [
            'name' => 'MeatHub Customer',
            'email' => 'customer@example.com',
            'password' => 'secure-password',
            'password_confirmation' => 'secure-password',
        ])->assertRedirect(route('account.orders'));

        $customer = User::query()->where('email', 'customer@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($customer);
        $this->assertFalse($customer->is_admin);

        $product = Product::create([
            'name' => 'Pork Belly',
            'price' => 320,
            'stock_kg' => 4,
            'is_available' => true,
        ]);

        $this->postJson(route('orders.store'), [
            'delivery_address' => '123 Market Street, Manila',
            'items' => [
                ['product_id' => $product->id, 'quantity_kg' => 1],
            ],
        ])->assertCreated();

        $order = Order::query()->firstOrFail();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertDatabaseCount('notifications', 0);

        $order->update(['payment_status' => 'paid']);

        $notification = $customer->notifications()->firstOrFail();
        $this->assertSame('filament', $notification->data['format']);
        $this->assertSame('MH-00000001', $notification->data['order_number']);
        $this->assertSame('pending', $notification->data['previous_status']);
        $this->assertSame('paid', $notification->data['order_status']);
        $this->assertSame('Your order MH-00000001 has been marked as Paid.', $notification->data['body']);

        $this->get(route('account.orders'))
            ->assertOk()
            ->assertDontSee('Order notifications')
            ->assertDontSee('Notifications')
            ->assertSee('← Back to MeatHub')
            ->assertSee('Sign out')
            ->assertSee('action="'.route('account.logout').'" method="POST"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('My orders')
            ->assertSee('Paid');

        $this->get('/')
            ->assertOk()
            ->assertSee('Your order MH-00000001 has been marked as Paid.')
            ->assertSee('1 unread notification')
            ->assertSee('Mark all as read');

        $this->get(route('account.orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Your order MH-00000001 is now Paid.');

        $this->from(route('home'))
            ->post(route('account.notifications.read-all'))
            ->assertRedirect(route('home'));
        $this->assertNotNull($customer->notifications()->findOrFail($notification->id)->read_at);
        $this->get('/')
            ->assertDontSee('1 unread notification')
            ->assertDontSee('Mark all as read');

        $this->get('/admin')->assertForbidden();

        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        Livewire::test(ListOrders::class)
            ->callTableAction('markCompleted', $order->fresh());

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'completed',
        ]);
        $this->assertDatabaseCount('notifications', 2);
        $this->assertTrue($customer->notifications()->get()->contains(
            fn ($storedNotification): bool => $storedNotification->data['previous_status'] === 'paid'
                && $storedNotification->data['order_status'] === 'completed',
        ));
    }

    public function test_guest_order_status_changes_do_not_create_customer_notifications(): void
    {
        $order = Order::create([
            'order_number' => 'MH-00000010',
            'delivery_address' => '123 Market Street, Manila',
            'total' => 100,
            'payment_status' => 'pending',
        ]);

        $order->update(['payment_status' => 'cancelled']);

        $this->assertDatabaseCount('notifications', 0);
    }

    public function test_guest_checkout_redirects_to_login_then_returns_to_checkout(): void
    {
        Product::create([
            'name' => 'Pork Belly',
            'price' => 320,
            'stock_kg' => 4,
            'is_available' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Pork Belly')
            ->assertSee('Add to Cart');

        $this->get(route('checkout'))
            ->assertRedirect(route('account.login'));
        $this->postJson(route('orders.store'), [
            'delivery_address' => '123 Market Street, Manila',
            'items' => [
                ['product_id' => 1, 'quantity_kg' => 1],
            ],
        ])->assertUnauthorized();

        $customer = User::factory()->create([
            'email' => 'checkout@example.com',
        ]);
        $customer->is_admin = false;
        $customer->save();

        $this->post(route('account.login.store'), [
            'email' => 'checkout@example.com',
            'password' => 'password',
        ])->assertRedirect(route('checkout'));

        $this->assertAuthenticatedAs($customer);
        $this->get(route('checkout'))
            ->assertRedirect(route('home', ['checkout' => '1']));
    }
}
