<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'producer_id',
        'category',
        'price',
        'stock_kg',
        'description',
        'image',
        'is_available',
    ];

    public function producer(): BelongsTo
    {
        return $this->belongsTo(Producer::class);
    }
}
