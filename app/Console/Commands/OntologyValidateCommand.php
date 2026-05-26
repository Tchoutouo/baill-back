<?php

namespace App\Console\Commands;

use App\Ontology\KnowledgeGraphService;
use App\Ontology\ShaclValidator;
use Illuminate\Console\Command;

class OntologyValidateCommand extends Command
{
    protected $signature   = 'ontology:validate {class? : Classe à valider (ex: Annonce)}';
    protected $description = 'Valide les nœuds du graphe contre les formes SHACL';

    public function handle(KnowledgeGraphService $kg, ShaclValidator $validator): int
    {
        $classes = $this->argument('class')
            ? [$this->argument('class')]
            : array_keys(config('agent.shacl_shapes', []));

        $totalViolations = 0;

        foreach ($classes as $class) {
            $shapes = config("agent.shacl_shapes.{$class}");
            if (!$shapes) {
                $this->warn("Aucune forme SHACL pour la classe « {$class} ».");
                continue;
            }

            $nodes      = $kg->queryByClass($class, [], 500);
            $violations = 0;
            $rows       = [];

            foreach ($nodes as $node) {
                $data   = $node['data'] ?? [];
                $result = $validator->validate($class, $data);

                if (!$result['valid']) {
                    $violations++;
                    foreach ($result['violations'] as $v) {
                        $rows[] = [$node['uri'], $v['field'], $v['message']];
                    }
                }
            }

            $count = count($nodes);
            $valid = $count - $violations;

            $this->line("─── <comment>{$class}</comment> : {$count} nœuds — <info>{$valid} valides</info>, <error>{$violations} violations</error>");

            if ($rows) {
                $this->table(['URI', 'Champ', 'Message'], $rows);
            }

            $totalViolations += $violations;
        }

        if ($totalViolations === 0) {
            $this->info("\n✔ Tous les nœuds sont conformes aux formes SHACL.");
            return self::SUCCESS;
        }

        $this->warn("\n⚠ {$totalViolations} nœud(s) non conformes détectés.");
        return self::FAILURE;
    }
}
