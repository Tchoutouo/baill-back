<?php

namespace App\Ontology;

use Illuminate\Support\Facades\Schema;

class OntologyGenerator
{
    private const XSD_MAP = [
        'bigint'    => 'xsd:integer',
        'integer'   => 'xsd:integer',
        'int'       => 'xsd:integer',
        'tinyint'   => 'xsd:integer',
        'smallint'  => 'xsd:integer',
        'float'     => 'xsd:decimal',
        'double'    => 'xsd:decimal',
        'decimal'   => 'xsd:decimal',
        'varchar'   => 'xsd:string',
        'char'      => 'xsd:string',
        'text'      => 'xsd:string',
        'longtext'  => 'xsd:string',
        'mediumtext' => 'xsd:string',
        'date'      => 'xsd:date',
        'datetime'  => 'xsd:dateTime',
        'timestamp' => 'xsd:dateTime',
        'boolean'   => 'xsd:boolean',
        'json'      => 'xsd:string',
    ];

    // Colonnes système exclues de toutes les classes
    private const SKIP_COLUMNS = [
        'id', 'created_at', 'updated_at', 'remember_token',
        'email_verified_at', 'deleted_at',
    ];

    public function generate(): string
    {
        $skip      = config('agent.ontology_skip_tables', []);
        $classMap  = config('agent.table_class_map', []);
        $baseUri   = config('agent.ontology.base_uri');

        $tables = collect(Schema::getTables())
            ->pluck('name')
            ->reject(fn($t) => in_array($t, $skip))
            ->values();

        $ttl  = $this->header($baseUri);
        $ttl .= $this->classes($tables, $classMap);
        $ttl .= $this->dataProperties($tables, $classMap);
        $ttl .= $this->objectProperties($tables, $classMap);

        return $ttl;
    }

    // ── Sections TTL ─────────────────────────────────────────────────────────

    private function header(string $baseUri): string
    {
        $date = now()->toIso8601String();

        return <<<TTL
        @prefix rdf:   <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .
        @prefix rdfs:  <http://www.w3.org/2000/01/rdf-schema#> .
        @prefix owl:   <http://www.w3.org/2002/07/owl#> .
        @prefix xsd:   <http://www.w3.org/2001/XMLSchema#> .
        @prefix baill: <{$baseUri}> .

        <{$baseUri}>
            a owl:Ontology ;
            rdfs:label "BailleurNet Ontologie" ;
            rdfs:comment "Générée automatiquement depuis le schéma MySQL — {$date}" .


        TTL;
    }

    private function classes(iterable $tables, array $classMap): string
    {
        $labels = [
            'Annonce'      => 'Annonce immobilière',
            'Annonceur'    => 'Annonceur / utilisateur',
            'Categorie'    => 'Catégorie d\'annonce',
            'SousCategorie'=> 'Sous-catégorie',
            'Abonnement'   => 'Forfait d\'abonnement',
            'Paiement'     => 'Paiement',
            'Photo'        => 'Photo d\'annonce',
            'ModePaiement' => 'Mode de paiement',
            'Profil'       => 'Profil utilisateur',
            'DroitAcces'   => 'Droit d\'accès',
        ];

        $out = "# ── Classes OWL ────────────────────────────────────────────────────────\n\n";

        foreach ($tables as $table) {
            $class = $classMap[$table] ?? $this->tableToClass($table);
            $label = $labels[$class] ?? $class;
            $out  .= "baill:{$class} a owl:Class ;\n    rdfs:label \"{$label}\" .\n\n";
        }

        return $out;
    }

    private function dataProperties(iterable $tables, array $classMap): string
    {
        $out = "# ── Data Properties ────────────────────────────────────────────────────\n\n";
        $seen = [];

        foreach ($tables as $table) {
            $class   = $classMap[$table] ?? $this->tableToClass($table);
            $columns = Schema::getColumns($table);

            foreach ($columns as $col) {
                $name = $col['name'];

                if (in_array($name, self::SKIP_COLUMNS))     continue;
                if (str_ends_with($name, '_id'))              continue;

                $prop  = $this->camelize($name);
                $range = $this->mapType($col['type_name'] ?? 'varchar');
                $key   = "{$prop}|{$class}";

                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $out .= "baill:{$prop} a owl:DatatypeProperty ;\n";
                $out .= "    rdfs:domain baill:{$class} ;\n";
                $out .= "    rdfs:range  {$range} .\n\n";
            }
        }

        return $out;
    }

    private function objectProperties(iterable $tables, array $classMap): string
    {
        $out = "# ── Object Properties (FK) ──────────────────────────────────────────────\n\n";

        $knownProps = [
            'user_id'        => ['prop' => 'publiePar',  'label' => 'publié par'],
            'abonnement_id'  => ['prop' => 'souscritA',  'label' => 'souscrit à'],
            'profil_id'      => ['prop' => 'aProfil',    'label' => 'a profil'],
            'annonce_id'     => ['prop' => 'appartientA','label' => 'appartient à'],
            'categorie_id'   => ['prop' => 'dansCateg',  'label' => 'dans catégorie'],
            'sous_categorie_id' => ['prop' => 'dansSousCateg', 'label' => 'dans sous-catégorie'],
            'access_right_id'   => ['prop' => 'aDroit',  'label' => 'a droit'],
        ];

        $seen = [];

        foreach ($tables as $table) {
            $domain = $classMap[$table] ?? $this->tableToClass($table);
            $fks    = Schema::getForeignKeys($table);

            foreach ($fks as $fk) {
                $col    = $fk['columns'][0] ?? null;
                $target = $fk['foreign_table'] ?? null;

                if (!$col || !$target) continue;

                $range = $classMap[$target] ?? $this->tableToClass($target);
                $info  = $knownProps[$col] ?? ['prop' => $this->camelize(str_replace('_id', '', $col)), 'label' => ''];
                $prop  = $info['prop'];
                $key   = "{$prop}|{$domain}";

                if (isset($seen[$key])) continue;
                $seen[$key] = true;

                $out .= "baill:{$prop} a owl:ObjectProperty ;\n";
                if ($info['label']) {
                    $out .= "    rdfs:label  \"{$info['label']}\" ;\n";
                }
                $out .= "    rdfs:domain baill:{$domain} ;\n";
                $out .= "    rdfs:range  baill:{$range} .\n\n";
            }
        }

        // Relations many-to-many modélisées explicitement
        $out .= "baill:appartientA a owl:ObjectProperty ;\n";
        $out .= "    rdfs:label  \"appartient à catégorie\" ;\n";
        $out .= "    rdfs:domain baill:Annonce ;\n";
        $out .= "    rdfs:range  baill:Categorie .\n\n";

        $out .= "baill:aPhoto a owl:ObjectProperty ;\n";
        $out .= "    rdfs:domain baill:Annonce ;\n";
        $out .= "    rdfs:range  baill:Photo .\n\n";

        return $out;
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function tableToClass(string $table): string
    {
        return str_replace('_', '', ucwords($table, '_'));
    }

    private function camelize(string $snake): string
    {
        $parts = explode('_', $snake);
        return lcfirst(implode('', array_map('ucfirst', $parts)));
    }

    private function mapType(string $sqlType): string
    {
        $base = strtolower(preg_replace('/\(.*\)/', '', $sqlType));
        return self::XSD_MAP[$base] ?? 'xsd:string';
    }
}
