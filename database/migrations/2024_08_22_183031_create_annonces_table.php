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
        if(!Schema::hasTable('annonces')){
            Schema::create('annonces', function (Blueprint $table) {
                $table->id();
                $table->string('title')->nullable();
                $table->string('subtitle')->nullable()->comment('sous-titre');
                $table->string('description')->nullable();
                $table->integer('price');
                $table->string('contact')->nullable();
                $table->string('country')->nullable();
                $table->string('neighborhood')->nullable();
                $table->string('location')->nullable()->comment('lieu_dit');
                // $table->boolean('is_published')->nullable()->default(false);
                $table->string('status')->nullable()->default('1');
                $table->string('is_forward')->nullable()->default('0');
                $table->timestamps();
            });
        }
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annonces');
    }
};
