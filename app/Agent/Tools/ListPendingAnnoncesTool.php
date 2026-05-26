<?php

namespace App\Agent\Tools;

use App\Models\Annonce;

class ListPendingAnnoncesTool implements ToolInterface
{
    public function name(): string { return 'list_pending_annonces'; }

    public function description(): string
    {
        return 'Liste les annonces en attente de validation (statut "encours"). '
             . 'Retourne les détails de chaque annonce avec les informations de l\'annonceur. '
             . 'Utilise cet outil pour modérer les nouvelles soumissions.';
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
                'query' => [
                    'type'        => 'string',
                    'description' => 'Filtrer par titre ou localisation',
                ],
            ],
            'required' => [],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $paginated = Annonce::with([
                'categories:id,title',
                'users:id,username,email,number',
                'abonnements:id,name,price,time,type_time',
            ])
            ->where('status', 'encours')
            ->when($input['query'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title',    'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(8, ['*'], 'page', (int) ($input['page'] ?? 1));

        return [
            'total'       => $paginated->total(),
            'page'        => $paginated->currentPage(),
            'total_pages' => $paginated->lastPage(),
            'annonces'    => $paginated->map(fn($a) => [
                'id'          => $a->id,
                'title'       => $a->title,
                'price'       => $a->price,
                'location'    => trim(($a->neighborhood ? $a->neighborhood . ', ' : '') . ($a->location ?? '')),
                'contact'     => $a->contact,
                'reference'   => $a->reference,
                'categories'  => $a->categories->pluck('title')->toArray(),
                'forfait'     => $a->abonnements
                    ? $a->abonnements->name . ' (' . $a->abonnements->price . ' FCFA / ' . $a->abonnements->time . ' ' . $a->abonnements->type_time . ')'
                    : null,
                'annonceur'   => $a->users ? [
                    'id'       => $a->users->id,
                    'username' => $a->users->username,
                    'email'    => $a->users->email,
                    'tel'      => $a->users->number,
                ] : null,
                'soumis_le'   => $a->created_at?->format('d/m/Y H:i'),
                'link'        => '/annonce/' . $a->id,
            ])->values()->toArray(),
        ];
    }
}
