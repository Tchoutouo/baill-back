<?php

namespace App\Agent\Tools;

use App\Models\Annonce;

class SearchAnnoncesTool implements ToolInterface
{
    public function name(): string { return 'search_annonces'; }

    public function description(): string
    {
        return 'Recherche des annonces immobilières sur bailleurnet.cm. '
             . 'Utilise les filtres disponibles pour affiner les résultats. '
             . 'Retourne une liste d\'annonces avec titre, prix, localisation et lien direct.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'query' => [
                    'type'        => 'string',
                    'description' => 'Mots-clés de recherche (ex: "villa", "F3", "meublé")',
                ],
                'categorie_id' => [
                    'type'        => 'integer',
                    'description' => 'Identifiant de la catégorie (obtenu via get_categories)',
                ],
                'localisation' => [
                    'type'        => 'string',
                    'description' => 'Ville ou quartier (ex: "Yaoundé", "Douala", "Bastos")',
                ],
                'prix_min' => [
                    'type'        => 'integer',
                    'description' => 'Prix minimum en FCFA',
                ],
                'prix_max' => [
                    'type'        => 'integer',
                    'description' => 'Prix maximum en FCFA',
                ],
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
        $query = Annonce::with([
                'categories:id,title',
                'pictures:id,annonce_id,location',
            ])
            ->where('status', '1')
            ->when($input['query'] ?? null, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('title',       'like', "%{$search}%")
                        ->orWhere('description',  'like', "%{$search}%")
                        ->orWhere('location',      'like', "%{$search}%")
                        ->orWhere('neighborhood',  'like', "%{$search}%");
                });
            })
            ->when($input['localisation'] ?? null, function ($q, $loc) {
                $q->where(function ($sub) use ($loc) {
                    $sub->where('location',    'like', "%{$loc}%")
                        ->orWhere('neighborhood', 'like', "%{$loc}%")
                        ->orWhere('country',       'like', "%{$loc}%");
                });
            })
            ->when($input['prix_min'] ?? null, fn($q, $v) => $q->where('price', '>=', $v))
            ->when($input['prix_max'] ?? null, fn($q, $v) => $q->where('price', '<=', $v))
            ->when($input['categorie_id'] ?? null, function ($q, $catId) {
                $q->whereHas('categories', fn($c) => $c->where('categories.id', $catId));
            })
            ->orderByDesc('is_forward')
            ->orderByDesc('created_at')
            ->paginate(5, ['*'], 'page', (int) ($input['page'] ?? 1));

        if ($query->total() === 0) {
            return [
                'total'    => 0,
                'annonces' => [],
                'message'  => 'Aucune annonce trouvée pour ces critères.',
            ];
        }

        return [
            'total'        => $query->total(),
            'page'         => $query->currentPage(),
            'total_pages'  => $query->lastPage(),
            'annonces'     => $query->map(fn($a) => $this->format($a))->values()->toArray(),
        ];
    }

    private function format(Annonce $a): array
    {
        return [
            'id'          => $a->id,
            'title'       => $a->title,
            'subtitle'    => $a->subtitle,
            'price'       => $a->price,
            'location'    => trim(($a->neighborhood ? $a->neighborhood . ', ' : '') . ($a->location ?? '')),
            'country'     => $a->country,
            'contact'     => $a->contact,
            'reference'   => $a->reference,
            'is_forward'  => (bool) $a->is_forward,
            'photo'       => $a->pictures->first()?->location,
            'categories'  => $a->categories->map(fn($c) => ['id' => $c->id, 'title' => $c->title])->toArray(),
            'link'        => '/annonce/' . $a->id,
        ];
    }
}
