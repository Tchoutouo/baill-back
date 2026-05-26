<?php

namespace App\Agent\Tools;

use App\Models\Annonce;
use App\Ontology\KnowledgeGraphService;
use App\Ontology\ShaclValidator;
use Illuminate\Support\Collection;
use Throwable;

class SubmitAnnonceTool implements ToolInterface
{
    public function __construct(
        private readonly ShaclValidator        $validator,
        private readonly KnowledgeGraphService $kg,
    ) {}

    public function name(): string { return 'submit_annonce'; }

    public function description(): string
    {
        return 'Crée l\'annonce dans la base de données après validation et confirmation explicite de l\'annonceur. '
             . 'L\'annonce sera en statut "en cours de validation". '
             . 'N\'appelle cet outil QUE lorsque l\'utilisateur a explicitement confirmé vouloir soumettre.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'draft' => [
                    'type'        => 'object',
                    'description' => 'Brouillon complet validé de l\'annonce',
                ],
            ],
            'required' => ['draft'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $userId = $context['user_id'] ?? null;
        if (!$userId) {
            return [
                'success' => false,
                'error'   => 'Vous devez être connecté pour publier une annonce.',
            ];
        }

        $draft      = (array) ($input['draft'] ?? []);
        $validation = $this->validator->validate('Annonce', $draft);

        if (!$validation['valid']) {
            $errors = collect($validation['violations'])->pluck('message')->implode(' | ');
            return [
                'success' => false,
                'error'   => "Le brouillon contient encore des erreurs : {$errors}",
            ];
        }

        $annonce          = new Annonce([
            'user_id'      => $userId,
            'title'        => $draft['title'],
            'subtitle'     => $draft['subtitle'] ?? null,
            'description'  => $draft['description'] ?? null,
            'price'        => (int) $draft['price'],
            'location'     => $draft['location'],
            'neighborhood' => $draft['neighborhood'] ?? null,
            'contact'      => $draft['contact'],
            'abonnement_id'=> (int) $draft['abonnement_id'],
            'reference'    => 'BN-' . strtoupper(substr(uniqid(), -6)),
        ]);
        $annonce->status  = 'encours';
        $annonce->save();

        if (!empty($draft['categories'])) {
            $annonce->categories()->sync($draft['categories']);
        }

        try {
            $this->kg->syncAnnonce($annonce->load(['categories', 'pictures', 'users']));
        } catch (Throwable) {
            // KG sync non-critique
        }

        return [
            'success'    => true,
            'annonce_id' => $annonce->id,
            'reference'  => $annonce->reference,
            'message'    => "Votre annonce \"{$annonce->title}\" a été soumise avec succès (réf : {$annonce->reference}). Elle sera publiée après validation par notre équipe.",
            'submission' => [
                'id'        => $annonce->id,
                'title'     => $annonce->title,
                'reference' => $annonce->reference,
                'status'    => 'encours',
            ],
        ];
    }
}
