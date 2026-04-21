<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add product_name column if missing
        if (!Schema::hasColumn('wholesale_storages', 'product_name')) {
            Schema::table('wholesale_storages', function (Blueprint $table) {
                $table->string('product_name')->nullable()->after('product_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('wholesale_storages', function (Blueprint $table) {
            if (Schema::hasColumn('wholesale_storages', 'product_name')) {
                $table->dropColumn('product_name');
            }
        });
    }
};
