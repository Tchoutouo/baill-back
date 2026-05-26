<?php

namespace App\Ontology;

use App\Models\Abonnement;
use App\Models\Annonce;
use App\Models\Categorie;
use PDO;

class KnowledgeGraphService
{
    private ?PDO $pdo = null;

    // ── Connexion & schéma ────────────────────────────────────────────────────

    private function db(): PDO
    {
        if ($this->pdo) return $this->pdo;

        $path = config('agent.ontology.kg_path');
        $dir  = dirname($path);

        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        $this->initSchema();

        return $this->pdo;
    }

    private function initSchema(): void
    {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS triples (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                subject   TEXT NOT NULL,
                predicate TEXT NOT NULL,
                object    TEXT NOT NULL,
                dtype     TEXT NOT NULL DEFAULT 'xsd:string'
            );
            CREATE INDEX IF NOT EXISTS idx_tri_sub  ON triples(subject);
            CREATE INDEX IF NOT EXISTS idx_tri_pred ON triples(predicate);
            CREATE INDEX IF NOT EXISTS idx_tri_sub_pred ON triples(subject, predicate);

            CREATE TABLE IF NOT EXISTS node_index (
                uri        TEXT PRIMARY KEY,
                class      TEXT NOT NULL,
                label      TEXT,
                mysql_id   INTEGER,
                data       TEXT,
                synced_at  INTEGER DEFAULT (strftime('%s','now'))
            );
            CREATE INDEX IF NOT EXISTS idx_node_class ON node_index(class);
            CREATE INDEX IF NOT EXISTS idx_node_mysql ON node_index(mysql_id, class);
        ");
    }

    // ── API publique — nœuds ─────────────────────────────────────────────────

    public function upsertNode(string $class, int $mysqlId, string $label, array $data = []): void
    {
        $uri = $this->uri($class, $mysqlId);
        $db  = $this->db();

        $db->prepare("
            INSERT INTO node_index (uri, class, label, mysql_id, data, synced_at)
            VALUES (:uri, :class, :label, :id, :data, strftime('%s','now'))
            ON CONFLICT(uri) DO UPDATE SET
                label = excluded.label,
                data  = excluded.data,
                synced_at = excluded.synced_at
        ")->execute([
            ':uri'   => $uri,
            ':class' => $class,
            ':label' => $label,
            ':id'    => $mysqlId,
            ':data'  => json_encode($data, JSON_UNESCAPED_UNICODE),
        ]);
    }

    public function removeNode(string $class, int $mysqlId): void
    {
        $uri = $this->uri($class, $mysqlId);
        $db  = $this->db();

        $db->prepare("DELETE FROM node_index WHERE uri = ?")->execute([$uri]);
        $db->prepare("DELETE FROM triples WHERE subject = ?")->execute([$uri]);
    }

    public function addTriple(string $subject, string $predicate, string $object, string $dtype = 'xsd:string'): void
    {
        $this->db()->prepare("
            INSERT INTO triples (subject, predicate, object, dtype)
            VALUES (?, ?, ?, ?)
        ")->execute([$subject, $predicate, $object, $dtype]);
    }

    public function clearTriplesForNode(string $uri): void
    {
        $this->db()->prepare("DELETE FROM triples WHERE subject = ?")->execute([$uri]);
    }

    // ── API publique — requêtes ───────────────────────────────────────────────

    /**
     * Recherche des nœuds d'une classe avec filtres optionnels sur les champs JSON.
     * $filters : ['prix_max' => 150000, 'ville' => 'Yaoundé']
     */
    public function queryByClass(string $class, array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $db    = $this->db();
        $where = ['class = :class'];
        $binds = [':class' => $class];

        foreach ($filters as $key => $value) {
            $placeholder = ':f_' . $key;
            $where[]     = "json_extract(data, '$.\"{$key}\"') = {$placeholder}";
            $binds[$placeholder] = $value;
        }

        $sql = 'SELECT uri, label, mysql_id, data FROM node_index WHERE '
             . implode(' AND ', $where)
             . ' ORDER BY synced_at DESC LIMIT :limit OFFSET :offset';

        $stmt = $db->prepare($sql);
        foreach ($binds as $k => $v) $stmt->bindValue($k, $v);
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(function (array $row) {
            $row['data'] = json_decode($row['data'] ?? '{}', true);
            return $row;
        }, $stmt->fetchAll());
    }

    public function findNode(string $class, int $mysqlId): ?array
    {
        $uri  = $this->uri($class, $mysqlId);
        $stmt = $this->db()->prepare('SELECT uri, label, mysql_id, data FROM node_index WHERE uri = ?');
        $stmt->execute([$uri]);
        $row  = $stmt->fetch();

        if (!$row) return null;
        $row['data'] = json_decode($row['data'] ?? '{}', true);
        return $row;
    }

    public function count(string $class): int
    {
        $stmt = $this->db()->prepare('SELECT COUNT(*) FROM node_index WHERE class = ?');
        $stmt->execute([$class]);
        return (int) $stmt->fetchColumn();
    }

    public function triplesFor(string $uri): array
    {
        $stmt = $this->db()->prepare('SELECT predicate, object, dtype FROM triples WHERE subject = ?');
        $stmt->execute([$uri]);
        return $stmt->fetchAll();
    }

    // ── Synchronisation complète depuis MySQL ─────────────────────────────────

    public function syncAll(): array
    {
        $stats = ['annonces' => 0, 'categories' => 0, 'abonnements' => 0];

        $stats['categories']  = $this->syncCategories();
        $stats['abonnements'] = $this->syncAbonnements();
        $stats['annonces']    = $this->syncAnnonces();

        return $stats;
    }

    public function syncAnnonce(Annonce $annonce): void
    {
        if ($annonce->status !== '1') {
            $this->removeNode('Annonce', $annonce->id);
            return;
        }

        $annonce->loadMissing(['categories', 'users', 'pictures']);
        $this->indexAnnonce($annonce);
    }

    // ── Sync helpers ─────────────────────────────────────────────────────────

    private function syncAnnonces(): int
    {
        $count = 0;
        Annonce::with(['categories:id,title', 'users:id,username,city,country', 'pictures:id,annonce_id,location'])
            ->where('status', '1')
            ->chunk(100, function ($annonces) use (&$count) {
                foreach ($annonces as $annonce) {
                    $this->indexAnnonce($annonce);
                    $count++;
                }
            });
        return $count;
    }

    private function syncCategories(): int
    {
        $count = 0;
        Categorie::with('sousCategorie:id,title')->chunk(50, function ($cats) use (&$count) {
            foreach ($cats as $cat) {
                $this->upsertNode('Categorie', $cat->id, $cat->title, [
                    'id'          => $cat->id,
                    'title'       => $cat->title,
                    'title_en'    => $cat->title_en,
                    'description' => $cat->description,
                    'sous_categories' => $cat->sousCategorie->map(fn($s) => [
                        'id' => $s->id, 'title' => $s->title,
                    ])->toArray(),
                ]);
                $count++;
            }
        });
        return $count;
    }

    private function syncAbonnements(): int
    {
        $count = 0;
        Abonnement::where('is_actived', true)->chunk(50, function ($abons) use (&$count) {
            foreach ($abons as $abon) {
                $this->upsertNode('Abonnement', $abon->id, $abon->name, [
                    'id'          => $abon->id,
                    'name'        => $abon->name,
                    'price'       => $abon->price,
                    'time'        => $abon->time,
                    'type_time'   => $abon->type_time,
                    'type'        => $abon->type,
                    'hight_lite'  => $abon->hight_lite,
                    'remise'      => $abon->remise,
                ]);
                $count++;
            }
        });
        return $count;
    }

    private function indexAnnonce(Annonce $annonce): void
    {
        $label  = $annonce->title ?? "Annonce #{$annonce->id}";
        $photos = $annonce->pictures->map(fn($p) => $p->location)->toArray();
        $cats   = $annonce->categories->map(fn($c) => ['id' => $c->id, 'title' => $c->title])->toArray();

        $data = [
            'id'          => $annonce->id,
            'title'       => $annonce->title,
            'subtitle'    => $annonce->subtitle,
            'description' => $annonce->description,
            'price'       => $annonce->price,
            'contact'     => $annonce->contact,
            'country'     => $annonce->country,
            'location'    => $annonce->location,
            'neighborhood'=> $annonce->neighborhood,
            'reference'   => $annonce->reference,
            'is_forward'  => $annonce->is_forward,
            'expiration_date' => $annonce->expiration_date,
            'categories'  => $cats,
            'photos'      => $photos,
            'user' => $annonce->users ? [
                'id'      => $annonce->users->id,
                'username'=> $annonce->users->username,
                'city'    => $annonce->users->city,
                'country' => $annonce->users->country,
            ] : null,
        ];

        $this->upsertNode('Annonce', $annonce->id, $label, $data);

        // Triples RDF pour les relations navigables
        $uri = $this->uri('Annonce', $annonce->id);
        $this->clearTriplesForNode($uri);

        $this->addTriple($uri, 'baill:price', (string) $annonce->price, 'xsd:integer');
        $this->addTriple($uri, 'baill:location', (string) $annonce->location, 'xsd:string');

        foreach ($annonce->categories as $cat) {
            $this->addTriple($uri, 'baill:appartientA', $this->uri('Categorie', $cat->id), 'uri');
        }
    }

    // ── Utilitaires ───────────────────────────────────────────────────────────

    public function uri(string $class, int $id): string
    {
        $slug = strtolower($class);
        return config('agent.ontology.data_uri') . "{$slug}/{$id}";
    }

    public function clearAll(): void
    {
        $db = $this->db();
        $db->exec('DELETE FROM node_index');
        $db->exec('DELETE FROM triples');
    }
}
