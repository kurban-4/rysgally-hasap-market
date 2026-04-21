<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('wholesale_storages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('received_price', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);  
            $table->string('batch_number')->nullable();
            $table->date('expiry_date')->nullable();
                        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wholesale_storages');
    }
};
