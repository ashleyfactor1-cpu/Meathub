<?php

use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\OrderController;
use App\Models\Producer;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/checkout', fn () => redirect()->route('home', ['checkout' => '1']))
    ->middleware('auth')
    ->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])
    ->middleware('auth')
    ->name('orders.store');
Route::get('/orders/{orderNumber}', [OrderController::class, 'show'])
    ->middleware('signed')
    ->name('orders.show');
Route::post('/orders/{orderNumber}/payment-reference', [OrderController::class, 'submitPaymentReference'])
    ->middleware('signed')
    ->name('orders.payment-reference');

Route::middleware('guest')->prefix('account')->name('account.')->group(function () {
    Route::get('/register', [CustomerAccountController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [CustomerAccountController::class, 'register'])->name('register.store');
    Route::get('/login', [CustomerAccountController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAccountController::class, 'login'])->name('login.store');
});

Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::post('/logout', [CustomerAccountController::class, 'logout'])->name('logout');
    Route::get('/chat', [CustomerAccountController::class, 'chat'])->name('chat');
    Route::get('/orders', [CustomerAccountController::class, 'orders'])->name('orders');
    Route::get('/orders/{orderNumber}', [OrderController::class, 'showForCustomer'])->name('orders.show');
    Route::post('/notifications/read-all', [CustomerAccountController::class, 'markAllNotificationsAsRead'])
        ->name('notifications.read-all');
});

Route::get('/', function (Request $request) {
    $search = trim((string) $request->query('q', ''));
    $category = (string) $request->query('category', '');
    $sort = (string) $request->query('sort', 'featured');

    $productsQuery = Product::query();

    if ($search !== '') {
        $productsQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%")
                ->orWhereHas('producer', fn ($producerQuery) => $producerQuery->where('name', 'like', "%{$search}%"))
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($category !== '') {
        $productsQuery->where('category', $category);
    }

    if ($sort === 'price-asc') {
        $productsQuery->orderBy('price');
    } elseif ($sort === 'price-desc') {
        $productsQuery->orderByDesc('price');
    } else {
        $productsQuery->latest();
    }

    return view('home', [
        'products' => $productsQuery->with('producer')->paginate(12)->appends(array_filter([
            'q' => $search ?: null,
            'category' => $category ?: null,
            'sort' => $sort !== 'featured' ? $sort : null,
        ])),
        'categories' => ['Pork', 'Beef', 'Chicken', 'Organ Meat'],
        'search' => $search,
        'selectedCategory' => $category,
        'sort' => $sort,
        'gcashQrImage' => config('services.gcash.qr_image'),
        'producers' => Producer::query()->where('is_active', true)->orderBy('name')->get(),
    ]);
})->name('home');
