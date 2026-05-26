<?php

namespace App\Agent\Tools;

use App\Ontology\ShaclValidator;
use Illuminate\Support\Collection;

class ValidateAnnonceDraftTool implements ToolInterface
{
    public function __construct(private readonly ShaclValidator $validator) {}

    public function name(): string { return 'validate_annonce_draft'; }

    public function description(): string
    {
        return 'Effectue une validation complète du brouillon d\'annonce avant soumission. '
             . 'Retourne les erreurs détaillées et confirme si le brouillon est prêt à être soumis. '
             . 'Utilise cet outil avant de demander la confirmation finale à l\'utilisateur.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'draft' => [
                    'type'        => 'object',
                    'description' => 'Brouillon complet de l\'annonce à valider',
                ],
            ],
            'required' => ['draft'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $draft  = (array) ($input['draft'] ?? []);
        $result = $this->validator->validate('Annonce', $draft);

        $message = $result['valid']
            ? 'Le brouillon est complet et prêt à être soumis.'
            : 'Des informations sont manquantes ou incorrectes : '
              . collect($result['violations'])->pluck('message')->implode(' | ');

        return [
            'valid'      => $result['valid'],
            'violations' => $result['violations'],
            'draft'      => $draft,
            'message'    => $message,
        ];
    }
}
