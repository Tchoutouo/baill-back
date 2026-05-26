<?php

namespace App\Ontology;

class ShaclValidator
{
    /**
     * Valide un tableau de données contre la forme SHACL d'une classe.
     *
     * @return array{valid: bool, violations: array<array{field: string, label: string, message: string}>}
     */
    public function validate(string $className, array $data): array
    {
        $shapes     = config("agent.shacl_shapes.{$className}", []);
        $violations = [];

        foreach ($shapes as $field => $rules) {
            $value     = $data[$field] ?? null;
            $label     = $rules['label'] ?? $field;
            $isPresent = $value !== null && $value !== '' && $value !== [];

            if (!empty($rules['required']) && !$isPresent) {
                $violations[] = $this->v($field, $label, "Le champ « {$label} » est requis.");
                continue;
            }

            if (!$isPresent) continue;

            $type = $rules['type'] ?? 'string';

            match ($type) {
                'integer' => $this->checkInteger($field, $label, $value, $rules, $violations),
                'email'   => $this->checkEmail($field, $label, $value, $violations),
                'array'   => $this->checkArray($field, $label, $value, $rules, $violations),
                default   => $this->checkString($field, $label, $value, $rules, $violations),
            };
        }

        return ['valid' => empty($violations), 'violations' => $violations];
    }

    /**
     * Retourne les champs manquants ou invalides pour une étape de création guidée.
     * Utile pour l'agent : il sait exactement quelle info demander ensuite.
     */
    public function missingFields(string $className, array $data): array
    {
        $result = $this->validate($className, $data);
        return array_column($result['violations'], 'field');
    }

    /**
     * Retourne un message formaté pour l'agent, listant les problèmes détectés.
     */
    public function humanSummary(string $className, array $data): ?string
    {
        $result = $this->validate($className, $data);
        if ($result['valid']) return null;

        $lines = array_map(fn($v) => "- {$v['message']}", $result['violations']);
        return implode("\n", $lines);
    }

    // ── Règles de validation ──────────────────────────────────────────────────

    private function checkString(string $field, string $label, mixed $value, array $rules, array &$out): void
    {
        if (!is_string($value)) {
            $out[] = $this->v($field, $label, "« {$label} » doit être une chaîne de caractères.");
            return;
        }

        if (isset($rules['min_length']) && mb_strlen($value) < $rules['min_length']) {
            $out[] = $this->v($field, $label,
                "« {$label} » doit contenir au moins {$rules['min_length']} caractères.");
        }

        if (isset($rules['max_length']) && mb_strlen($value) > $rules['max_length']) {
            $out[] = $this->v($field, $label,
                "« {$label} » ne doit pas dépasser {$rules['max_length']} caractères.");
        }

        if (isset($rules['pattern']) && !preg_match('/' . $rules['pattern'] . '/', $value)) {
            $out[] = $this->v($field, $label, "« {$label} » a un format invalide.");
        }
    }

    private function checkInteger(string $field, string $label, mixed $value, array $rules, array &$out): void
    {
        if (!is_numeric($value)) {
            $out[] = $this->v($field, $label, "« {$label} » doit être un nombre entier.");
            return;
        }

        $int = (int) $value;

        if (isset($rules['min']) && $int < $rules['min']) {
            $out[] = $this->v($field, $label,
                "« {$label} » doit être supérieur ou égal à {$rules['min']}.");
        }

        if (isset($rules['max']) && $int > $rules['max']) {
            $out[] = $this->v($field, $label,
                "« {$label} » doit être inférieur ou égal à {$rules['max']}.");
        }
    }

    private function checkEmail(string $field, string $label, mixed $value, array &$out): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $out[] = $this->v($field, $label, "« {$label} » n'est pas une adresse e-mail valide.");
        }
    }

    private function checkArray(string $field, string $label, mixed $value, array $rules, array &$out): void
    {
        if (!is_array($value)) {
            $out[] = $this->v($field, $label, "« {$label} » doit être une liste.");
            return;
        }

        if (isset($rules['min_items']) && count($value) < $rules['min_items']) {
            $out[] = $this->v($field, $label,
                "« {$label} » doit contenir au moins {$rules['min_items']} élément(s).");
        }
    }

    // ── Fabrique de violation ─────────────────────────────────────────────────

    private function v(string $field, string $label, string $message): array
    {
        return ['field' => $field, 'label' => $label, 'message' => $message];
    }
}
