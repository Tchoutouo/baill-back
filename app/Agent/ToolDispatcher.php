<?php

namespace App\Agent;

use App\Agent\Tools\ToolInterface;
use RuntimeException;

class ToolDispatcher
{
    /** @var array<string, ToolInterface> */
    private array $registry = [];

    public function __construct(private readonly RoleGuard $roleGuard) {}

    public function register(ToolInterface $tool): void
    {
        $this->registry[$tool->name()] = $tool;
    }

    /**
     * Retourne les définitions Claude (tool_use format) pour les outils autorisés.
     * Si $allowed contient '*', tous les outils enregistrés sont inclus.
     */
    public function definitions(array $allowed): array
    {
        return collect($this->registry)
            ->filter(fn($tool, $name) => in_array('*', $allowed) || in_array($name, $allowed))
            ->map(fn(ToolInterface $tool) => [
                'name'         => $tool->name(),
                'description'  => $tool->description(),
                'input_schema' => $tool->inputSchema(),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Exécute un outil en vérifiant l'autorisation côté serveur (défense en profondeur).
     * Même si le LLM ne reçoit que les outils autorisés, on revalide ici.
     */
    public function execute(string $name, array $input, string $role, array $context = []): array
    {
        if (!isset($this->registry[$name])) {
            throw new RuntimeException("Outil inconnu : {$name}");
        }

        if (!$this->roleGuard->canUse($role, $name)) {
            return ['error' => "Action non autorisée : l'outil « {$name} » n'est pas disponible pour votre rôle."];
        }

        return $this->registry[$name]->execute($input, $context);
    }

    public function all(): array
    {
        return array_keys($this->registry);
    }
}
