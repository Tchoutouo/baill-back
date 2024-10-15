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
                $table->string('title');
                $table->string('subtitle')->comment('sous-titre');
                $table->string('description');
                $table->integer('price');
                $table->string('contact');
                $table->string('country');
                $table->string('neighborhood');
                $table->string('location')->comment('lieu_dit');
                $table->boolean('is_published')->nullable()->default(false);
                $table->string('status');
                $table->string('is_forward');
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
