<?php

namespace App\Agent\Tools;

use App\Models\Annonce;

class RejectAnnonceTool implements ToolInterface
{
    public function name(): string { return 'reject_annonce'; }

    public function description(): string
    {
        return 'Rejette une annonce en attente ou publiée avec une raison optionnelle. '
             . 'Demande toujours confirmation à l\'administrateur avant d\'exécuter cette action.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'annonce_id' => [
                    'type'        => 'integer',
                    'description' => 'Identifiant de l\'annonce à rejeter',
                ],
                'raison' => [
                    'type'        => 'string',
                    'description' => 'Motif du rejet (optionnel, sera communiqué à l\'annonceur)',
                ],
            ],
            'required' => ['annonce_id'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $annonce = Annonce::find((int) $input['annonce_id']);

        if (!$annonce) {
            return ['success' => false, 'error' => 'Annonce introuvable.'];
        }

        if ($annonce->status === 'rejected') {
            return ['success' => false, 'error' => 'Cette annonce est déjà rejetée.'];
        }

        $annonce->status = 'rejected';
        $annonce->save();

        $raison  = $input['raison'] ?? null;
        $message = "L'annonce \"{$annonce->title}\" (réf : {$annonce->reference}) a été rejetée.";
        if ($raison) {
            $message .= " Motif : {$raison}.";
        }

        return [
            'success'    => true,
            'annonce_id' => $annonce->id,
            'title'      => $annonce->title,
            'reference'  => $annonce->reference,
            'raison'     => $raison,
            'message'    => $message,
        ];
    }
}
