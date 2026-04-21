<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Используем 'licenses' с буквой S, чтобы всё было по стандарту
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->boolean('is_activated')->default(false);
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};