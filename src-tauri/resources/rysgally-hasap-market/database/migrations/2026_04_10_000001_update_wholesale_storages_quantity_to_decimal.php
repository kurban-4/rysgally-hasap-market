<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Allow decimal quantities (needed for weight-based items: kg)
        Schema::table('wholesale_storages', function (Blueprint $table) {
            $table->decimal('quantity', 12, 3)->default(0)->change();
        });

        // Track unit_type per invoice line so weight logic works
        Schema::table('wholesale_items', function (Blueprint $table) {
            if (!Schema::hasColumn('wholesale_items', 'unit_type')) {
                $table->string('unit_type')->default('piece')->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('wholesale_storages', function (Blueprint $table) {
            $table->integer('quantity')->default(0)->change();
        });

        Schema::table('wholesale_items', function (Blueprint $table) {
            if (Schema::hasColumn('wholesale_items', 'unit_type')) {
                $table->dropColumn('unit_type');
            }
        });
    }
};
