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
        Schema::create('apartment_ratings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('apartment_id')->constrained('apartments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('rating',1,1); // Assuming rating is between 1-5
            $table->text('comment')->nullable();

            $table->unique(['apartment_id', 'user_id']); // One rating per user per apartment
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_ratings');
    }
};
