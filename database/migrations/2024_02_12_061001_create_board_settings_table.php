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
        Schema::create('board_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->text('description')->nullable();
            $table->boolean('required')->default(false);
            $table->foreignIdFor(\App\Models\Board::class);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('board_settings');
    }
};
