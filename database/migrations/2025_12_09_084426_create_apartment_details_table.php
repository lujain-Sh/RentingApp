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
        Schema::create('apartment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_id')->constrained('apartments')->onDelete('cascade');
            $table->integer('number_of_bedrooms');
            $table->integer('number_of_bathrooms');
            $table->integer('area_sq_meters');
            $table->decimal('rent_price_per_night', 10, 2);
            $table->text('description_ar');
            $table->text('description_en');
            $table->boolean('has_balcony');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartment_details');
    }
};
