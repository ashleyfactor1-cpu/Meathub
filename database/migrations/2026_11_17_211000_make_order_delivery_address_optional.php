<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_address')->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNull('delivery_address')
            ->update(['delivery_address' => 'Pickup reservation']);

        Schema::table('orders', function (Blueprint $table) {
            $table->text('delivery_address')->nullable(false)->change();
        });
    }
};
