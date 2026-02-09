<?php

use App\Enums\Governorate;
use App\Models\City;
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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('governorate')->unsigned();
            // $table->tinyInteger('governorate');
            $table->foreignId('city_id')->constrained('cities');
            $table->string('street');
            $table->string('building_number');
            $table->string('floor');
            $table->string('apartment_number');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
