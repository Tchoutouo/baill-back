<?php

namespace App\Agent\Tools;

use App\Ontology\ShaclValidator;

class CreateAnnonceStepTool implements ToolInterface
{
    public function __construct(private readonly ShaclValidator $validator) {}

    public function name(): string { return 'create_annonce_step'; }

    public function description(): string
    {
        return 'Enregistre les informations fournies par l\'annonceur pour créer une annonce, '
             . 'valide les données saisies et retourne l\'état du brouillon avec les champs manquants. '
             . 'Appelle cet outil à chaque fois que l\'utilisateur fournit de nouveaux détails pour son annonce.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'current_draft' => [
                    'type'        => 'object',
                    'description' => 'État actuel du brouillon (accumulé au fil de la conversation, peut être vide {})',
                ],
                'updates' => [
                    'type'        => 'object',
                    'description' => 'Nouvelles informations à ajouter ou corriger dans le brouillon',
                    'properties'  => [
                        'title'         => ['type' => 'string',  'description' => 'Titre de l\'annonce'],
                        'subtitle'      => ['type' => 'string',  'description' => 'Sous-titre ou accroche'],
                        'description'   => ['type' => 'string',  'description' => 'Description détaillée'],
                        'price'         => ['type' => 'integer', 'description' => 'Prix en FCFA'],
                        'location'      => ['type' => 'string',  'description' => 'Ville ou localité'],
                        'neighborhood'  => ['type' => 'string',  'description' => 'Quartier'],
                        'contact'       => ['type' => 'string',  'description' => 'Numéro de téléphone ou email'],
                        'categories'    => [
                            'type'        => 'array',
                            'items'       => ['type' => 'integer'],
                            'description' => 'Liste des IDs de catégories (obtenu via get_categories)',
                        ],
                        'abonnement_id' => ['type' => 'integer', 'description' => 'ID du forfait (obtenu via get_abonnements)'],
                    ],
                ],
            ],
            'required' => ['current_draft', 'updates'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $draft   = array_merge((array) ($input['current_draft'] ?? []), (array) ($input['updates'] ?? []));
        $missing = $this->validator->missingFields('Annonce', $draft);
        $summary = $this->validator->humanSummary('Annonce', $draft);

        return [
            'draft'              => $draft,
            'missing_fields'     => $missing,
            'is_complete'        => empty($missing),
            'validation_message' => $summary,
        ];
    }
}
