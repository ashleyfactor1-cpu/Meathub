<?php

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    $search = trim((string) $request->query('q', ''));
    $category = (string) $request->query('category', '');
    $sort = (string) $request->query('sort', 'featured');

    $productsQuery = Product::query();

    if ($search !== '') {
        $productsQuery->where(function ($query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('producer_name', 'like', "%{$search}%")
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
        'products' => $productsQuery->paginate(12)->withQueryString(),
        'categories' => ['Pork', 'Beef', 'Chicken', 'Organ Meat'],
        'search' => $search,
        'selectedCategory' => $category,
        'sort' => $sort,
    ]);
})->name('home');
