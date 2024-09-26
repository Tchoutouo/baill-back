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
        if(!Schema::hasTable('categorie_sous_categories')){
            Schema::create('categorie_sous_categories', function (Blueprint $table) {
                $table->id();
                if (!Schema::hasColumn('categories','categorie_id')) {
                    $table->unsignedBigInteger('categorie_id');
                    $table->foreign('categorie_id')->references('id')->on('categories');
                }
                if (!Schema::hasColumn('sous_categories','sous_categorie_id')) {
                    $table->unsignedBigInteger('sous_categorie_id');
                    $table->foreign('sous_categorie_id')->references('id')->on('sous_categories');
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
        Schema::dropIfExists('categorie_sous_categorie');
    }
};
