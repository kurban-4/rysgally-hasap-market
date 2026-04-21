<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::create('shifts', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Продавец
        $table->timestamp('opened_at'); // Время открытия смены
        $table->timestamp('closed_at')->nullable(); // Время закрытия (изначально пусто)
        $table->decimal('total_revenue', 10, 2)->default(0); // Выручка за эту смену
        $table->enum('status', ['active', 'closed'])->default('active'); // Статус
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
