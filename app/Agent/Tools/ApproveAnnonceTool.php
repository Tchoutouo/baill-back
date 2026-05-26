<?php

namespace App\Agent\Tools;

use App\Models\Annonce;
use App\Ontology\KnowledgeGraphService;
use Carbon\Carbon;
use Throwable;

class ApproveAnnonceTool implements ToolInterface
{
    public function __construct(private readonly KnowledgeGraphService $kg) {}

    public function name(): string { return 'approve_annonce'; }

    public function description(): string
    {
        return 'Approuve une annonce en attente de validation et la publie sur la plateforme. '
             . 'La date d\'expiration est calculée à partir du forfait de l\'annonceur. '
             . 'Demande toujours confirmation à l\'administrateur avant d\'exécuter cette action.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'annonce_id' => [
                    'type'        => 'integer',
                    'description' => 'Identifiant de l\'annonce à approuver',
                ],
            ],
            'required' => ['annonce_id'],
        ];
    }

    public function execute(array $input, array $context = []): array
    {
        $annonce = Annonce::with('abonnements')->find((int) $input['annonce_id']);

        if (!$annonce) {
            return ['success' => false, 'error' => 'Annonce introuvable.'];
        }

        if ($annonce->status === '1') {
            return ['success' => false, 'error' => 'Cette annonce est déjà publiée.'];
        }

        $expiration = $this->computeExpiration($annonce);

        $annonce->status          = '1';
        $annonce->expiration_date = $expiration;
        $annonce->save();

        try {
            $this->kg->syncAnnonce($annonce->load(['categories', 'pictures', 'users']));
        } catch (Throwable) {
            // KG sync non-critique
        }

        return [
            'success'         => true,
            'annonce_id'      => $annonce->id,
            'title'           => $annonce->title,
            'reference'       => $annonce->reference,
            'expiration_date' => $expiration?->format('d/m/Y'),
            'message'         => "L'annonce \"{$annonce->title}\" (réf : {$annonce->reference}) a été approuvée et publiée jusqu'au " . ($expiration?->format('d/m/Y') ?? 'indéterminé') . '.',
        ];
    }

    private function computeExpiration(Annonce $annonce): ?Carbon
    {
        $abo = $annonce->abonnements;

        if (!$abo || !$abo->time) {
            return Carbon::now()->addDays(30);
        }

        $unit = match (strtolower($abo->type_time ?? '')) {
            'semaines', 'semaine'    => 'weeks',
            'mois'                   => 'months',
            'années', 'annee', 'ans' => 'years',
            default                  => 'days',
        };

        return Carbon::now()->add($unit, (int) $abo->time);
    }
}
