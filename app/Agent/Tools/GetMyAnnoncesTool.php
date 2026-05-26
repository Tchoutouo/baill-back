<?php

namespace App\Agent\Tools;

use App\Models\Annonce;

class GetMyAnnoncesTool implements ToolInterface
{
    public function name(): string { return 'get_my_annonces'; }

    public function description(): string
    {
        return 'Retourne la liste des annonces publiées par l\'annonceur connecté, '
             . 'avec leur statut et les détails principaux. '
             . 'Utilise cet outil quand l\'utilisateur demande à voir ses propres annonces.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'page' => [
                    'type'        => 'integer',
                    'description' => 'Numéro de page (défaut 1)',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $userId = $context['user_id'] ?? null;
        if (!$userId) {
            return ['error' => 'Vous devez être connecté pour accéder à vos annonces.'];
        }

        $paginated = Annonce::with([
                'categories:id,title',
                'pictures:id,annonce_id,location',
            ])
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->paginate(5, ['*'], 'page', (int) ($input['page'] ?? 1));

        $statusLabels = [
            '1'        => 'Publiée',
            '0'        => 'Expirée',
            'encours'  => 'En attente de validation',
            'rejected' => 'Rejetée',
        ];

        return [
            'total'       => $paginated->total(),
            'page'        => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
            'annonces'    => $paginated->map(fn($a) => [
                'id'         => $a->id,
                'title'      => $a->title,
                'price'      => $a->price,
                'location'   => trim(($a->neighborhood ? $a->neighborhood . ', ' : '') . ($a->location ?? '')),
                'status'     => $a->status,
                'status_label' => $statusLabels[$a->status] ?? $a->status,
                'reference'  => $a->reference,
                'categories' => $a->categories->map(fn($c) => ['id' => $c->id, 'title' => $c->title])->toArray(),
                'photo'      => $a->pictures->first()?->location,
                'link'       => '/annonce/' . $a->id,
            ])->values()->toArray(),
        ];
    }
}
