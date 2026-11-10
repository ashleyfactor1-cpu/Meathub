<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'producer_name',
        'category',
        'price',
        'stock_kg',
        'description',
        'image',
        'is_available',
    ];
}
