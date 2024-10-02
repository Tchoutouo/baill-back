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
        if(Schema::hasTable('pictures')){
            Schema::table('pictures', function (Blueprint $table) {
                if(!Schema::hasColumn('annonces','annonce_id')){
                    $table->unsignedBigInteger('annonce_id');
                    $table->foreign('annonce_id')->references('id')->on('annonces');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        
    }
};
