<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('storages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // barcode для партии (опционально)
            $table->string('barcode')->nullable();

            // quantity: хранит pcs для piece и kg (decimal:3) для weight
            $table->decimal('quantity', 12, 3)->default(0);

            $table->string('category')->nullable();
            $table->date('expiry_date')->nullable();

            // цены и метаданные партии
            $table->decimal('received_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->integer('discount')->default(0);
            $table->string('batch_number')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('storages');
    }
};
