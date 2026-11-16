<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('producers')
            ->where('name', 'Alcatea Victoria')
            ->update(['name' => 'Alcate Victoria']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('producers')
            ->where('name', 'Alcate Victoria')
            ->update(['name' => 'Alcatea Victoria']);
    }
};
