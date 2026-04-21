<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'units_per_box')) {
                $table->integer('units_per_box')->default(1)->after('unit_type');
            }
            if (!Schema::hasColumn('products', 'total_quantity_units')) {
                $table->decimal('total_quantity_units', 15, 3)->default(0)->after('units_per_box');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'units_per_box')) {
                $table->dropColumn('units_per_box');
            }
            if (Schema::hasColumn('products', 'total_quantity_units')) {
                $table->dropColumn('total_quantity_units');
            }
        });
    }
};
