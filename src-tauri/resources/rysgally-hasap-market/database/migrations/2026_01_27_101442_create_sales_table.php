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

            
            $table->decimal('quantity', 12, 3)->default(0);

            
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            
            $table->enum('sale_type', ['piece', 'weight'])->default('piece');

            
            $table->string('transaction_id')->nullable()->index();

            
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
