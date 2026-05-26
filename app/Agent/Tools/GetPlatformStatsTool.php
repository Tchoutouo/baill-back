<?php

namespace App\Agent\Tools;

use Illuminate\Support\Facades\DB;

class GetPlatformStatsTool implements ToolInterface
{
    public function name(): string { return 'get_platform_stats'; }

    public function description(): string
    {
        return 'Retourne les statistiques globales de la plateforme bailleurnet.cm : '
             . 'nombre d\'annonces par statut, utilisateurs, catégories, et activité récente.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [],
            'required'   => [],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $annoncesByStatus = DB::table('annonces')
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($row) => [$row->status => (int) $row->total])
            ->toArray();

        $statusLabels = [
            '1'        => 'publiées',
            '0'        => 'expirées',
            'encours'  => 'en_attente',
            'rejected' => 'rejetées',
        ];

        $annoncesFormatted = [];
        foreach ($annoncesByStatus as $status => $count) {
            $key = $statusLabels[$status] ?? $status;
            $annoncesFormatted[$key] = $count;
        }

        $totalUsers      = (int) DB::table('users')->count();
        $totalCategories = (int) DB::table('categories')->count();
        $newLast7Days    = (int) DB::table('annonces')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();
        $newUsersLast7   = (int) DB::table('users')
            ->where('created_at', '>=', now()->subDays(7))
            ->count();

        $topCategories = DB::table('categorie_annonces')
            ->join('categories', 'categories.id', '=', 'categorie_annonces.categorie_id')
            ->selectRaw('categories.title, COUNT(*) as total')
            ->groupBy('categories.id', 'categories.title')
            ->orderByDesc('total')
            ->limit(5)
            ->get()
            ->map(fn($r) => ['categorie' => $r->title, 'annonces' => (int) $r->total])
            ->values()
            ->toArray();

        return [
            'annonces'          => $annoncesFormatted,
            'total_annonces'    => array_sum($annoncesFormatted),
            'utilisateurs'      => $totalUsers,
            'categories'        => $totalCategories,
            'nouvelles_7j'      => $newLast7Days,
            'nouveaux_users_7j' => $newUsersLast7,
            'top_categories'    => $topCategories,
        ];
    }
}
