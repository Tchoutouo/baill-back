<?php

namespace App\Agent\Tools;

use App\Models\Categorie;

class GetCategoriesTool implements ToolInterface
{
    public function name(): string { return 'get_categories'; }

    public function description(): string
    {
        return 'Retourne la liste complète des catégories d\'annonces disponibles sur bailleurnet.cm '
             . '(appartements, villas, terrains, bureaux, etc.) avec leurs sous-catégories. '
             . 'Utilise cet outil pour aider l\'utilisateur à identifier la catégorie qui correspond à sa recherche.';
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
        $categories = Categorie::with('sousCategorie:id,title')
            ->orderBy('title')
            ->get(['id', 'title', 'title_en', 'description']);

        return [
            'categories' => $categories->map(fn($c) => [
                'id'             => $c->id,
                'title'          => $c->title,
                'title_en'       => $c->title_en,
                'description'    => $c->description,
                'sous_categories'=> $c->sousCategorie->map(fn($s) => [
                    'id'    => $s->id,
                    'title' => $s->title,
                ])->toArray(),
            ])->values()->toArray(),
        ];
    }
}
