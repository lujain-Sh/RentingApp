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
        Schema::create('phone_sensitives', function (Blueprint $table) {

            $table->id();

            $table->string('country_code',4); 
            $table->string('phone_number',9); 
            $table->string('full_phone_str');

            $table->unique(['country_code','phone_number'], 'uq_country_code_phone');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('phone_sensitives');
    }
};
