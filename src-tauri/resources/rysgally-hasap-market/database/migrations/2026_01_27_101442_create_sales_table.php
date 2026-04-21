<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // quantity хранится в тех же единицах, что и storage.quantity (pcs или kg)
            $table->decimal('quantity', 12, 3)->default(0);

            // цена за единицу и итог
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            // тип продажи: piece или weight
            $table->enum('sale_type', ['piece', 'weight'])->default('piece');

            // идентификатор транзакции/чека
            $table->string('transaction_id')->nullable()->index();

            // опционально: имя покупателя, касса (till)
            $table->string('customer_name')->nullable();
            $table->foreignId('till_id')->nullable()->constrained('tills')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
