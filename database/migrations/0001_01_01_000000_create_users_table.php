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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            
            $table->foreignId('phone_sensitive_id')
      ->constrained('phone_sensitives');


            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            
            $table->string('legal_doc_url');
            $table->string('legal_photo_url');
            
            $table->string('password');
            
            $table->boolean('is_phone_number_validated')->default(true);//tmp: it should be false);
            $table->boolean('is_active')->default(true); // active acc : user pending/approved // deactive : rejected
            $table->boolean('is_admin_validated')->nullable();   // TRUE = approved by admin
            $table->unique(['phone_sensitive_id','is_active','is_phone_number_validated'],'uq_user_active_validated_phone_number');

            $table->timestamps();

        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
