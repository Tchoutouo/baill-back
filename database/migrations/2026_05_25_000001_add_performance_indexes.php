<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Index sur annonces — colonnes fréquemment filtrées/triées
        Schema::table('annonces', function (Blueprint $table) {
            if (!$this->hasIndex('annonces', 'annonces_user_id_status_index')) {
                $table->index(['user_id', 'status'], 'annonces_user_id_status_index');
            }
            if (!$this->hasIndex('annonces', 'annonces_created_at_index')) {
                $table->index('created_at', 'annonces_created_at_index');
            }
            if (!$this->hasIndex('annonces', 'annonces_status_index')) {
                $table->index('status', 'annonces_status_index');
            }
        });

        // Index sur paiements — filtres sur statut + date fréquents dans les KPIs
        Schema::table('paiements', function (Blueprint $table) {
            if (!$this->hasIndex('paiements', 'paiements_statut_date_index')) {
                $table->index(['statut', 'date_paiement'], 'paiements_statut_date_index');
            }
        });

        // Index sur users — filtres par profil_id et country
        Schema::table('users', function (Blueprint $table) {
            if (!$this->hasIndex('users', 'users_profil_id_index')) {
                $table->index('profil_id', 'users_profil_id_index');
            }
            if (!$this->hasIndex('users', 'users_created_at_index')) {
                $table->index('created_at', 'users_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('annonces', function (Blueprint $table) {
            $table->dropIndex('annonces_user_id_status_index');
            $table->dropIndex('annonces_created_at_index');
            $table->dropIndex('annonces_status_index');
        });

        Schema::table('paiements', function (Blueprint $table) {
            $table->dropIndex('paiements_statut_date_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_profil_id_index');
            $table->dropIndex('users_created_at_index');
        });
    }

    private function hasIndex(string $table, string $index): bool
    {
        return collect(\DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
