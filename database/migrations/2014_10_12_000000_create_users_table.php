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
            $table->string('name')->nullable();
            $table->string('username')->unique();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('picture')->nullable();//why integer
            $table->string('number')->nullable();
            $table->string('whatsapp_number');
            $table->string('site_url')->nullable();
            $table->string('neighborhood');//quartier
            $table->string('city');
            $table->string('country');
            $table->date('date_of_birth')->nullable()->comment('date de naissance');
            $table->date('place_of_birth')->nullable()->comment('lieu de naissance');
            $table->string('sex',1)->nullable()->comment('sex');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
