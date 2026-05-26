<?php

namespace App\Console\Commands;

use App\Ontology\OntologyGenerator;
use Illuminate\Console\Command;

class OntologyGenerateCommand extends Command
{
    protected $signature   = 'ontology:generate';
    protected $description = 'Génère le fichier TTL (OWL) depuis le schéma MySQL courant';

    public function handle(OntologyGenerator $generator): int
    {
        $path = config('agent.ontology.ttl_path');
        $dir  = dirname($path);

        if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
            $this->error("Impossible de créer le dossier : {$dir}");
            return self::FAILURE;
        }

        $this->info('Introspection du schéma MySQL…');

        $ttl = $generator->generate();
        file_put_contents($path, $ttl);

        $lines = substr_count($ttl, "\n");
        $this->info("Ontologie générée → {$path} ({$lines} lignes)");

        return self::SUCCESS;
    }
}
