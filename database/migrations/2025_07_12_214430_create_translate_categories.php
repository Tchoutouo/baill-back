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
        if(!Schema::hasTable('translate_categories')){
            Schema::create('translate_categories', function (Blueprint $table) {
                $table->id();
                if(!Schema::hasColumn( 'categories', 'categorie_id')){
                    $table->unsignedBigInteger('categorie_id');
                    $table->foreign('categorie_id')->references('id')->on('categories');
                }
                $table->string('title');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translate_categories');
    }
};
