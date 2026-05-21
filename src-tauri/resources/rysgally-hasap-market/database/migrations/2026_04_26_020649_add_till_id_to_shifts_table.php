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
        if (!Schema::hasColumn('shifts', 'till_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->foreignId('till_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shifts', 'till_id')) {
            Schema::table('shifts', function (Blueprint $table) {
                $table->dropForeignIdFor(\App\Models\Till::class);
            });
        }
    }
};