<?php

namespace App\Agent\Tools;

use App\Models\User;

class SearchUsersAdminTool implements ToolInterface
{
    public function name(): string { return 'search_users_admin'; }

    public function description(): string
    {
        return 'Recherche des utilisateurs de la plateforme par nom, username ou email. '
             . 'Retourne leurs informations de profil et le nombre d\'annonces publiées.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'query' => [
                    'type'        => 'string',
                    'description' => 'Terme de recherche (nom, username ou email)',
                ],
                'page' => [
                    'type'        => 'integer',
                    'description' => 'Numéro de page (défaut 1)',
                ],
            ],
            'required' => ['query'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $search = trim($input['query'] ?? '');

        $paginated = User::with('profils:id,code,name')
            ->withCount('annonces')
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('username', 'like', "%{$search}%")
                        ->orWhere('email',    'like', "%{$search}%")
                        ->orWhere('name',     'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name',  'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(10, ['*'], 'page', (int) ($input['page'] ?? 1));

        return [
            'total'       => $paginated->total(),
            'page'        => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
            'users'       => $paginated->map(fn($u) => [
                'id'             => $u->id,
                'username'       => $u->username,
                'email'          => $u->email,
                'nom'            => trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) ?: ($u->name ?? null),
                'tel'            => $u->number,
                'ville'          => $u->city,
                'role'           => $u->profils?->name ?? 'Annonceur',
                'annonces_count' => $u->annonces_count,
                'inscrit_le'     => $u->created_at?->format('d/m/Y'),
            ])->values()->toArray(),
        ];
    }
}
