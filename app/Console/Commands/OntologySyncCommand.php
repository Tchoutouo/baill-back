<?php

namespace App\Console\Commands;

use App\Ontology\KnowledgeGraphService;
use Illuminate\Console\Command;

class OntologySyncCommand extends Command
{
    protected $signature   = 'ontology:sync {--fresh : Vide le graphe avant la synchronisation}';
    protected $description = 'Synchronise le graphe de connaissance SQLite depuis MySQL';

    public function handle(KnowledgeGraphService $kg): int
    {
        if ($this->option('fresh')) {
            $kg->clearAll();
            $this->info('Graphe vidé.');
        }

        $this->info('Synchronisation en cours…');
        $start = microtime(true);

        $stats = $kg->syncAll();

        $elapsed = round(microtime(true) - $start, 2);

        $this->table(
            ['Entité', 'Nœuds synchronisés'],
            [
                ['Annonces',    $stats['annonces']],
                ['Catégories',  $stats['categories']],
                ['Abonnements', $stats['abonnements']],
            ]
        );

        $total = array_sum($stats);
        $this->info("✔ {$total} nœuds synchronisés en {$elapsed}s.");

        return self::SUCCESS;
    }
}
