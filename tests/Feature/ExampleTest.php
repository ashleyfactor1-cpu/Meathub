<?php

namespace Tests\Feature;

use App\Filament\Pages\CustomerChats;
use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\ProducerResource\Pages\CreateProducer as CreateProducerPage;
use App\Filament\Resources\ProducerResource\Pages\ListProducers as ListProducersPage;
use App\Filament\Widgets\MeatHubStatsOverview;
use App\Livewire\CustomerChat;
use App\Models\ChatMessage;
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
            ->assertSee('Victoria, Oriental Mindoro');
    }

    public function test_storefront_cart_toggle_and_count_are_in_the_header_only(): void
    {
        $html = $this->get('/')->assertOk()->getContent();
        $cartTogglePosition = strpos($html, 'id="cart-toggle"');
        $cartDialogPosition = strpos($html, 'id="cart-dialog"');
        $exploreProductsPosition = strpos($html, 'Explore products');

        $this->assertNotFalse($cartTogglePosition);
        $this->assertNotFalse($cartDialogPosition);
        $this->assertNotFalse($exploreProductsPosition);
        $this->assertGreaterThan($exploreProductsPosition, $cartTogglePosition);
        $this->assertLessThan($cartDialogPosition, $cartTogglePosition);
        $this->assertSame(1, substr_count($html, 'id="cart-toggle"'));
        $this->assertSame(1, substr_count($html, 'id="cart-count"'));
        $this->assertStringNotContainsString('fixed bottom-5 right-4', $html);
        $cartButton = substr($html, $cartTogglePosition, strpos($html, '</button>', $cartTogglePosition) - $cartTogglePosition);
        $this->assertStringContainsString('🛒', $cartButton);
        $this->assertStringNotContainsString('>Cart<', $cartButton);
        $this->assertStringNotContainsString('absolute', $cartButton);
    }

    public function test_customer_chat_link_is_floating_and_labeled_chat(): void
    {
        $customer = User::factory()->create();
        $customer->is_admin = false;
        $customer->save();
        $this->actingAs($customer);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.route('account.chat').'"', false)
            ->assertSee('fixed bottom-5 right-4', false)
            ->assertSee('Chat')
            ->assertDontSee('Chat support');
    }

    public function test_header_places_explore_first_and_notifications_next_to_cart(): void
    {
        $customer = User::factory()->create();
        $customer->is_admin = false;
        $customer->save();
        $this->actingAs($customer);

        $html = $this->get('/')->assertOk()->getContent();
        $explorePosition = strpos($html, 'Explore products');
        $accountPosition = strpos($html, 'My account');
        $cartPosition = strpos($html, 'id="cart-toggle"');
        $notificationPosition = strpos($html, 'aria-label="Notifications');

        $this->assertNotFalse($explorePosition);
        $this->assertNotFalse($accountPosition);
        $this->assertNotFalse($cartPosition);
        $this->assertNotFalse($notificationPosition);
        $this->assertLessThan($accountPosition, $explorePosition);
        $this->assertLessThan($notificationPosition, $cartPosition);
        $exploreLink = substr($html, $explorePosition, strpos($html, '</a>', $explorePosition) - $explorePosition);
        $this->assertStringNotContainsString('↗', $exploreLink);
    }

    public function test_marketplace_shows_product_details_and_category_filters(): void
    {
        $producer = Producer::create([
            'name' => 'San Isidro Farm',
            'location' => 'Bulacan',
            'address' => '123 Farm Road, Bulacan',
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
            ->assertSee('Organ Meat')
            ->assertSee('123 Farm Road, Bulacan')
            ->assertSee('https://maps.google.com/maps?q=123+Farm+Road%2C+Bulacan&amp;output=embed', false)
            ->assertSee('h-64 w-full', false)
            ->assertSee('href="#producer-'.$producer->id.'"', false);
    }

    public function test_search_shows_matching_products_and_jumps_to_results(): void
    {
        $producer = Producer::create(['name' => 'San Isidro Farm', 'is_active' => true]);
        Product::create([
            'name' => 'Pork Chop',
            'producer_id' => $producer->id,
            'category' => 'Pork',
            'price' => 250,
            'stock_kg' => 5,
            'is_available' => true,
        ]);
        Product::create([
            'name' => 'Beef Ribs',
            'producer_id' => $producer->id,
            'category' => 'Beef',
            'price' => 450,
            'stock_kg' => 3,
            'is_available' => true,
        ]);
        Product::create([
            'name' => 'Leg quarter',
            'producer_id' => $producer->id,
            'category' => 'Chicken',
            'price' => 220,
            'stock_kg' => 4,
            'is_available' => true,
        ]);

        $response = $this->get(route('home', ['q' => 'chop']));

        $response->assertOk()
            ->assertSee('Pork Chop')
            ->assertDontSee('Leg quarter')
            ->assertDontSee('Beef Ribs')
            ->assertSee('Showing 1 result for “chop”')
            ->assertSee('action="'.route('home').'#market"', false);

        $this->get(route('home', ['q' => 'chicken']))
            ->assertOk()
            ->assertSee('Leg quarter')
            ->assertDontSee('Pork Chop')
            ->assertDontSee('Beef Ribs')
            ->assertSee('Showing 1 result for “chicken”');
    }

    public function test_checkout_is_for_pickup_reservations_without_a_delivery_address(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Pickup reservation')
            ->assertDontSee('name="delivery_address"', false)
            ->assertDontSee('Delivery address');
    }

    public function test_custom_google_maps_embed_url_is_used_on_the_producer_profile(): void
    {
        Producer::create([
            'name' => 'Mapped Farm',
            'address' => '456 Market Road, Manila',
            'map_embed_url' => 'https://www.google.com/maps/embed?pb=map-token',
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('https://www.google.com/maps/embed?pb=map-token', false);
    }

    public function test_producer_admin_form_and_table_include_map_fields(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateProducerPage::class)
            ->assertFormFieldExists('address')
            ->assertFormFieldExists('map_embed_url');

        Livewire::test(CreateProducerPage::class)
            ->fillForm([
                'name' => 'Invalid Map Farm',
                'map_embed_url' => 'https://example.com/maps/embed',
            ])
            ->call('create')
            ->assertHasFormErrors(['map_embed_url']);

        Livewire::test(CreateProducerPage::class)
            ->fillForm([
                'name' => 'Address Only Farm',
                'address' => '123 Farm Road, Bulacan',
                'map_embed_url' => null,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('producers', [
            'name' => 'Address Only Farm',
            'address' => '123 Farm Road, Bulacan',
            'map_embed_url' => null,
        ]);

        Livewire::test(ListProducersPage::class)
            ->assertCanRenderTableColumn('address');
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

    public function test_admin_order_details_show_pickup_fulfillment_without_delivery_address(): void
    {
        config(['app.env' => 'local']);
        $this->actingAs(User::factory()->create());

        $order = Order::create([
            'order_number' => 'MH-00000001',
            'delivery_address' => null,
            'total' => 250,
            'payment_status' => 'pending',
        ]);

        Livewire::test(ListOrders::class)
            ->mountTableAction('view', $order)
            ->assertSee('Fulfillment')
            ->assertSee('Pickup reservation')
            ->assertDontSee('Delivery address');
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
            'delivery_address' => null,
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

        $producer = Producer::create([
            'name' => 'San Isidro Farm',
            'address' => '123 Farm Road, Bulacan',
            'is_active' => true,
        ]);
        $product = Product::create([
            'name' => 'Pork Belly',
            'producer_id' => $producer->id,
            'price' => 320,
            'stock_kg' => 4,
            'is_available' => true,
        ]);

        $this->postJson(route('orders.store'), [
            'items' => [
                ['product_id' => $product->id, 'quantity_kg' => 1],
            ],
        ])->assertCreated();

        $order = Order::query()->firstOrFail();
        $this->assertSame($customer->id, $order->user_id);
        $this->assertNull($order->delivery_address);
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
            ->assertDontSee('Chat support')
            ->assertDontSee('href="'.route('account.orders').'"', false)
            ->assertSee('Paid');

        $this->get('/')
            ->assertOk()
            ->assertSee('Your order MH-00000001 has been marked as Paid.')
            ->assertSee('1 unread notification')
            ->assertSee('Mark all as read');

        $this->get(route('account.orders.show', $order->order_number))
            ->assertOk()
            ->assertSee('Your order MH-00000001 is now Paid.')
            ->assertSee('Producer shop / pick-up locations')
            ->assertSee('123 Farm Road, Bulacan')
            ->assertSee('https://maps.google.com/maps?q=123+Farm+Road%2C+Bulacan&amp;output=embed', false);

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

    public function test_customer_can_chat_with_support_without_access_to_other_customer_messages(): void
    {
        $customer = User::factory()->create();
        $customer->is_admin = false;
        $customer->save();

        $otherCustomer = User::factory()->create();
        $otherCustomer->is_admin = false;
        $otherCustomer->save();

        ChatMessage::create([
            'customer_id' => $otherCustomer->id,
            'sender_id' => $otherCustomer->id,
            'body' => 'Private message from another customer.',
        ]);

        $this->actingAs($customer);

        $this->get(route('account.chat'))
            ->assertOk()
            ->assertSee('Chat with us')
            ->assertSee('Back to MeatHub')
            ->assertDontSee('My orders')
            ->assertDontSee('Sign out');
        $this->get(route('filament.admin.pages.customer-chats'))->assertForbidden();

        Livewire::test(CustomerChat::class)
            ->set('body', 'I need help with my order.')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSee('I need help with my order.')
            ->assertDontSee('Private message from another customer.');

        $this->assertDatabaseHas('chat_messages', [
            'customer_id' => $customer->id,
            'sender_id' => $customer->id,
            'body' => 'I need help with my order.',
        ]);
    }

    public function test_admin_can_view_customer_chats_and_reply(): void
    {
        $customer = User::factory()->create();
        $customer->is_admin = false;
        $customer->save();

        ChatMessage::create([
            'customer_id' => $customer->id,
            'sender_id' => $customer->id,
            'body' => 'Can you help me?',
        ]);

        $this->actingAs(User::factory()->create());

        $this->get(route('account.chat'))->assertForbidden();
        $this->get(route('filament.admin.pages.customer-chats'))
            ->assertOk()
            ->assertSee($customer->name)
            ->assertSee('Can you help me?');

        Livewire::test(CustomerChats::class)
            ->assertSee($customer->name)
            ->assertSee('Can you help me?')
            ->set('body', 'Of course, how can we help?')
            ->call('sendMessage')
            ->assertHasNoErrors()
            ->assertSee('Of course, how can we help?');

        $this->assertDatabaseHas('chat_messages', [
            'customer_id' => $customer->id,
            'body' => 'Of course, how can we help?',
        ]);
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
