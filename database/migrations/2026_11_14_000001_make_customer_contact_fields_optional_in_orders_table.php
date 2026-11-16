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
            $table->string('customer_name')->nullable()->change();
            $table->string('customer_phone', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('orders')
            ->whereNull('customer_name')
            ->update(['customer_name' => '']);
        DB::table('orders')
            ->whereNull('customer_phone')
            ->update(['customer_phone' => '']);

        Schema::table('orders', function (Blueprint $table) {
            $table->string('customer_name')->nullable(false)->change();
            $table->string('customer_phone', 32)->nullable(false)->change();
        });
    }
};
