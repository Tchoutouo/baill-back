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
        if(!Schema::hasTable('user_access_rights')){
            Schema::create('user_access_rights', function (Blueprint $table) {
                $table->id();
                if(!Schema::hasColumn('users','user_id')){
                    $table->unsignedBigInteger('user_id');
                    $table->foreign('user_id')->references('id')->on('users');
                }
                if(!Schema::hasColumn('access_rights','access_right_id')){
                    $table->unsignedBigInteger('access_right_id');
                    $table->foreign('access_right_id')->references('id')->on('access_rights');
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
        Schema::dropIfExists('user_access_rights');
    }
};
