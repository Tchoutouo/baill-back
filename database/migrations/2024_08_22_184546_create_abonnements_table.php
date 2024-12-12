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
        if(!Schema::hasTable('abonnements')){
            Schema::create('abonnements', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->integer('time');
                $table->string('type_time');
                $table->integer('price');
                $table->string('type');
                $table->boolean('is_actived')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('abonnements');
    }
};
