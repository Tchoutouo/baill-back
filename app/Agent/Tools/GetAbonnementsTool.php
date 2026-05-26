<?php

namespace App\Agent\Tools;

use App\Models\Abonnement;

class GetAbonnementsTool implements ToolInterface
{
    public function name(): string { return 'get_abonnements'; }

    public function description(): string
    {
        return 'Retourne la liste des forfaits d\'abonnement disponibles pour publier des annonces sur bailleurnet.cm. '
             . 'Utilise cet outil quand l\'utilisateur demande combien coûte la publication d\'une annonce '
             . 'ou quelles sont les offres disponibles.';
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
        $abonnements = Abonnement::where('is_actived', true)
            ->orderBy('price')
            ->get(['id', 'name', 'price', 'time', 'type_time', 'type', 'hight_lite', 'remise']);

        return [
            'abonnements' => $abonnements->map(fn($a) => [
                'id'         => $a->id,
                'name'       => $a->name,
                'price'      => $a->price,
                'duree'      => $a->time . ' ' . $a->type_time,
                'type'       => $a->type,
                'en_vedette' => (bool) $a->hight_lite,
                'remise'     => $a->remise,
            ])->values()->toArray(),
        ];
    }
}
