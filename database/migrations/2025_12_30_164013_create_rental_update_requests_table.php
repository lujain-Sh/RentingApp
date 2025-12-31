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
        Schema::create('rental_update_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartment_rental_id')->constrained('apartment_rentals')->onDelete('cascade');
            $table->date('requested_start_date');
            $table->date('requested_end_date');
            $table->decimal('requested_total_price', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            // $table->text('rejection_reason')->nullable();
            $table->date('current_start_date');
            $table->date('current_end_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rental_update_requests');
    }
};
