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
        if(!Schema::hasTable('categorie_annonces')){
            Schema::create('categorie_annonces', function (Blueprint $table) {
                $table->id();
                if(!Schema::hasColumn( 'categories', 'categorie_id')){
                    $table->unsignedBigInteger('categorie_id');
                    $table->foreign('categorie_id')->references('id')->on('categories');
                }
                if(!Schema::hasColumn('annonces','annonce_id')){
                    $table->unsignedBigInteger('annonce_id');
                    $table->foreign('annonce_id')->references('id')->on('annonces');
                }
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categorie_annonces');
    }
};
