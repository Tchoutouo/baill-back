<?php

return [

    // ── Provider LLM (anthropic | mistral) ──────────────────────────────────
    'provider'   => env('AGENT_PROVIDER', 'anthropic'),
    'max_tokens' => (int) env('AGENT_MAX_TOKENS', 1024),

    // ── Anthropic ────────────────────────────────────────────────────────────
    'anthropic_key'   => env('ANTHROPIC_API_KEY'),
    'anthropic_model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),

    // ── Mistral ──────────────────────────────────────────────────────────────
    'mistral_key'   => env('MISTRAL_API_KEY'),
    'mistral_model' => env('MISTRAL_MODEL', 'mistral-large-latest'),

    // ── Chemins ontologie ────────────────────────────────────────────────────
    'ontology' => [
        'base_uri'   => 'http://bailleurnet.cm/ontology#',
        'data_uri'   => 'http://bailleurnet.cm/data#',
        'ttl_path'   => storage_path('ontology/bailleurnet.ttl'),
        'shacl_path' => storage_path('ontology/shacl_shapes.ttl'),
        'kg_path'    => storage_path('ontology/knowledge_graph.sqlite'),
    ],

    // Intervalle de re-synchronisation automatique du graphe (secondes)
    'sync_interval' => (int) env('AGENT_SYNC_INTERVAL', 600),

    // ── Tables ignorées par le générateur d'ontologie ────────────────────────
    'ontology_skip_tables' => [
        'migrations',
        'personal_access_tokens',
        'password_reset_tokens',
        'failed_jobs',
        'jobs',
        'subscriptions',
        'subscription_items',
        // tables pivot (les relations sont modélisées via ObjectProperties)
        'categorie_annonces',
        'categorie_sous_categories',
        'user_access_rights',
    ],

    // Correspondance table → nom de classe OWL
    'table_class_map' => [
        'users'          => 'Annonceur',
        'annonces'       => 'Annonce',
        'categories'     => 'Categorie',
        'sous_categories' => 'SousCategorie',
        'abonnements'    => 'Abonnement',
        'paiements'      => 'Paiement',
        'pictures'       => 'Photo',
        'mode_paiements' => 'ModePaiement',
        'profils'        => 'Profil',
        'access_rights'  => 'DroitAcces',
    ],

    // ── Formes SHACL (PHP — utilisées par ShaclValidator au runtime) ─────────
    'shacl_shapes' => [

        'Annonce' => [
            'title' => [
                'label'      => 'Titre',
                'required'   => true,
                'type'       => 'string',
                'max_length' => 255,
            ],
            'description' => [
                'label'    => 'Description',
                'required' => false,
                'type'     => 'string',
            ],
            'price' => [
                'label'    => 'Prix (FCFA)',
                'required' => true,
                'type'     => 'integer',
                'min'      => 0,
            ],
            'location' => [
                'label'    => 'Localisation / quartier',
                'required' => true,
                'type'     => 'string',
            ],
            'contact' => [
                'label'    => 'Contact',
                'required' => true,
                'type'     => 'string',
            ],
            'categories' => [
                'label'     => 'Catégories',
                'required'  => true,
                'type'      => 'array',
                'min_items' => 1,
            ],
            'abonnement_id' => [
                'label'    => 'Forfait',
                'required' => true,
                'type'     => 'integer',
                'min'      => 1,
            ],
        ],

        'Annonceur' => [
            'username' => [
                'label'      => "Nom d'utilisateur",
                'required'   => true,
                'type'       => 'string',
                'min_length' => 3,
                'max_length' => 50,
            ],
            'email' => [
                'label'    => 'Adresse e-mail',
                'required' => true,
                'type'     => 'email',
            ],
            'password' => [
                'label'      => 'Mot de passe',
                'required'   => true,
                'type'       => 'string',
                'min_length' => 8,
            ],
            'whatsapp_number' => [
                'label'    => 'Numéro WhatsApp',
                'required' => true,
                'type'     => 'string',
            ],
        ],

    ],

];
