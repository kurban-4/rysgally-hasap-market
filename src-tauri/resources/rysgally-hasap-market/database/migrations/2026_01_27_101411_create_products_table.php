<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            
            $table->string('name')->nullable();
            $table->string('product_code')->nullable()->index(); 
            $table->string('barcode')->nullable()->unique(); 
            $table->text('description')->nullable();
            $table->string('manufacturer')->nullable();

            
            $table->decimal('price', 10, 2)->nullable(); 
            $table->decimal('received_price', 10, 2)->nullable();
            $table->integer('discount')->default(0); 

            
            $table->string('category')->nullable();
            $table->date('produced_date')->nullable();
            $table->date('expiry_date')->nullable();

            
            $table->enum('unit_type', ['piece', 'weight'])->default('piece');
            $table->integer('units_per_box')->default(1);
            $table->decimal('total_quantity_units', 15, 3)->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
