<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wholesale_market_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->decimal('quantity', 15, 3);
            $table->string('unit_type', 32)->default('piece');
            $table->string('market_barcode', 255);
            $table->decimal('received_price', 12, 2);
            $table->decimal('selling_price', 12, 2);
            $table->unsignedTinyInteger('discount')->default(0);
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->foreignId('storage_id')->nullable()->constrained('storages')->nullOnDelete();
            $table->json('source_batches')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wholesale_market_transfers');
    }
};
