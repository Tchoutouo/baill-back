<?php

namespace App\Agent;

class RoleGuard
{
    private const ROLE_TOOLS = [
        'visitor' => [
            'search_annonces',
            'get_annonce_details',
            'get_categories',
            'get_abonnements',
        ],
        'advertiser' => [
            'search_annonces',
            'get_annonce_details',
            'get_categories',
            'get_abonnements',
            'create_annonce_step',
            'validate_annonce_draft',
            'submit_annonce',
            'get_my_annonces',
        ],
        'admin' => ['*'],
    ];

    /**
     * Retourne la liste des noms d'outils autorisés pour un rôle donné.
     * '*' signifie tous les outils enregistrés.
     */
    public function toolsFor(string $role): array
    {
        return self::ROLE_TOOLS[$role] ?? self::ROLE_TOOLS['visitor'];
    }

    public function canUse(string $role, string $toolName): bool
    {
        $allowed = $this->toolsFor($role);
        return in_array('*', $allowed) || in_array($toolName, $allowed);
    }

    /**
     * Détermine le rôle d'un utilisateur Eloquent.
     */
    public function roleFromUser(mixed $user): string
    {
        if (!$user) return 'visitor';

        $code = $user->profils?->code ?? $user->profil?->code ?? '';

        return match (true) {
            in_array($code, ['SUP_ADMIN', 'ADMIN']) => 'admin',
            default                                  => 'advertiser',
        };
    }
}
