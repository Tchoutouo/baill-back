<?php

namespace App\Agent\Tools;

use App\Models\Annonce;

class GetAnnonceTool implements ToolInterface
{
    public function name(): string { return 'get_annonce_details'; }

    public function description(): string
    {
        return 'Retourne les détails complets d\'une annonce immobilière à partir de son identifiant. '
             . 'Utilise cet outil quand l\'utilisateur demande plus d\'informations sur une annonce spécifique.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'type'        => 'integer',
                    'description' => 'Identifiant de l\'annonce',
                ],
            ],
            'required' => ['id'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $annonce = Annonce::with([
            'categories:id,title',
            'pictures:id,annonce_id,location',
            'users:id,username,city,country',
            'abonnements:id,name,type',
        ])->where('status', '1')->find((int) $input['id']);

        if (!$annonce) {
            return ['error' => 'Annonce introuvable ou non disponible.'];
        }

        return [
            'id'              => $annonce->id,
            'title'           => $annonce->title,
            'subtitle'        => $annonce->subtitle,
            'description'     => $annonce->description,
            'price'           => $annonce->price,
            'contact'         => $annonce->contact,
            'location'        => $annonce->location,
            'neighborhood'    => $annonce->neighborhood,
            'country'         => $annonce->country,
            'reference'       => $annonce->reference,
            'expiration_date' => $annonce->expiration_date,
            'is_forward'      => (bool) $annonce->is_forward,
            'categories'      => $annonce->categories->map(fn($c) => ['id' => $c->id, 'title' => $c->title])->toArray(),
            'photos'          => $annonce->pictures->pluck('location')->toArray(),
            'annonceur' => $annonce->users ? [
                'username' => $annonce->users->username,
                'city'     => $annonce->users->city,
                'country'  => $annonce->users->country,
            ] : null,
            'link' => '/annonce/' . $annonce->id,
        ];
    }
}
