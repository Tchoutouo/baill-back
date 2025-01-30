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
        if(!Schema::hasTable('mode_paiements')){
            Schema::create('mode_paiements', function (Blueprint $table) {
                $table->id();
                if (!Schema::hasColumn('mode_paiements','title')) {
                    $table->string('title');
                }
                if (!Schema::hasColumn('mode_paiements','is_active')) {
                    $table->boolean('is_active')->default(true);
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
        Schema::dropIfExists('mode_paiements');
    }
};
