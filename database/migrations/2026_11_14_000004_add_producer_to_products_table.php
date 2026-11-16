<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('producer_id')
                ->nullable()
                ->constrained()
                ->nullOnDelete();
        });

        $producerNames = DB::table('products')
            ->whereNotNull('producer_name')
            ->where('producer_name', '!=', '')
            ->distinct()
            ->pluck('producer_name');

        foreach ($producerNames as $name) {
            $producerId = DB::table('producers')->insertGetId([
                'name' => $name,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('products')
                ->where('producer_name', $name)
                ->update(['producer_id' => $producerId]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('producer_name');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('producer_name')->nullable();
        });

        $products = DB::table('products')
            ->join('producers', 'products.producer_id', '=', 'producers.id')
            ->select('products.id', 'producers.name')
            ->get();

        foreach ($products as $product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update(['producer_name' => $product->name]);
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('producer_id');
        });
    }
};
