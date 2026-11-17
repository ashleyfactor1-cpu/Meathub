<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;

class OrderController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:50'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity_kg' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        if (blank(config('services.gcash.qr_image'))) {
            throw ValidationException::withMessages([
                'items' => 'GCash payments are not available until the merchant QR code is configured.',
            ]);
        }

        $userId = $request->user()->id;

        $order = DB::transaction(function () use ($validated, $userId): Order {
            $requestedItems = collect($validated['items'])->keyBy('product_id');
            $products = Product::query()
                ->whereIn('id', $requestedItems->keys())
                ->with('producer')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $totalCents = 0;
            $orderItems = [];

            foreach ($requestedItems as $productId => $item) {
                $product = $products->get($productId);
                $quantity = (int) $item['quantity_kg'];

                if (! $product || ! $product->is_available || $product->stock_kg < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => 'A selected product is no longer available in the requested quantity. Please review your cart.',
                    ]);
                }

                $unitPriceCents = (int) round((float) $product->price * 100);
                $subtotalCents = $unitPriceCents * $quantity;
                $totalCents += $subtotalCents;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'producer_name' => $product->producer?->name,
                    'quantity_kg' => $quantity,
                    'unit_price' => number_format($unitPriceCents / 100, 2, '.', ''),
                    'subtotal' => number_format($subtotalCents / 100, 2, '.', ''),
                ];
            }

            $order = Order::create([
                'user_id' => $userId,
                'total' => number_format($totalCents / 100, 2, '.', ''),
                'stock_deducted' => true,
            ]);

            foreach ($orderItems as $item) {
                $products->get($item['product_id'])->decrement('stock_kg', $item['quantity_kg']);
            }

            $order->items()->createMany($orderItems);
            $order->update(['order_number' => sprintf('MH-%08d', $order->id)]);

            return $order;
        });

        $confirmationUrl = URL::temporarySignedRoute(
            'orders.show',
            now()->addDays(7),
            ['orderNumber' => $order->order_number],
        );

        if ($request->expectsJson()) {
            return response()->json([
                'redirect_url' => $confirmationUrl,
                'order_number' => $order->order_number,
            ], 201);
        }

        return redirect()->to($confirmationUrl);
    }

    public function show(string $orderNumber): View
    {
        $order = Order::query()
            ->with('items.product.producer')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return $this->renderOrder($order);
    }

    public function showForCustomer(Request $request, string $orderNumber): View
    {
        $order = $request->user()
            ->orders()
            ->with('items.product.producer')
            ->where('order_number', $orderNumber)
            ->firstOrFail();

        return $this->renderOrder($order);
    }

    private function renderOrder(Order $order): View
    {
        $producers = $order->items
            ->map(fn ($item) => $item->product?->producer)
            ->filter(fn ($producer) => $producer && $producer->mapEmbedSource())
            ->unique('id')
            ->values();

        return view('orders.show', [
            'order' => $order,
            'producers' => $producers,
            'gcashQrImage' => config('services.gcash.qr_image'),
            'paymentReferenceUrl' => URL::temporarySignedRoute(
                'orders.payment-reference',
                now()->addDays(7),
                ['orderNumber' => $order->order_number],
            ),
        ]);
    }

    public function submitPaymentReference(Request $request, string $orderNumber): RedirectResponse
    {
        $validated = $request->validate([
            'payment_reference' => ['required', 'string', 'max:100', 'unique:orders,payment_reference'],
            'gcash_receipt' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $receiptPath = $request->file('gcash_receipt')->store('receipts', 'public');

        if ($receiptPath === false) {
            throw new RuntimeException('The GCash receipt could not be stored.');
        }

        try {
            $updated = DB::transaction(function () use ($orderNumber, $validated, $receiptPath): bool {
                $order = Order::query()
                    ->where('order_number', $orderNumber)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($order->payment_status !== 'pending' || filled($order->payment_reference)) {
                    return false;
                }

                $order->update([
                    'payment_reference' => $validated['payment_reference'],
                    'gcash_receipt' => $receiptPath,
                ]);

                return true;
            });
        } catch (QueryException $exception) {
            Storage::disk('public')->delete($receiptPath);

            throw $exception;
        }

        if (! $updated) {
            Storage::disk('public')->delete($receiptPath);

            throw ValidationException::withMessages([
                'payment_reference' => 'This order is no longer awaiting payment verification.',
            ]);
        }

        return redirect()->to(URL::temporarySignedRoute(
            'orders.show',
            now()->addDays(7),
            ['orderNumber' => $orderNumber],
        ));
    }
}
