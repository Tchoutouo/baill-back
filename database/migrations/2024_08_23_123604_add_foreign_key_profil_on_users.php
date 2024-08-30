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
        if(Schema::hasTable('users')){
            Schema::table('users', function (Blueprint $table) {
                if(!Schema::hasColumn('profils','profil_id')){
                    $table->unsignedBigInteger('profil_id');
                    $table->foreign('profil_id')->references('id')->on('profils');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
