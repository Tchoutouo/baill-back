<?php

namespace App\Agent;

interface LlmClientInterface
{
    /**
     * Appel synchrone — retourne la réponse complète assemblée.
     *
     * @param  array $payload  Format normalisé interne
     *                         (system, max_tokens, tools, messages)
     * @return array{stop_reason: string, content: array, text: string}
     */
    public function messages(array $payload): array;

    /**
     * Appel streaming — appelle $onToken(string) pour chaque token texte reçu.
     * Retourne la réponse assemblée une fois le stream terminé.
     *
     * @return array{stop_reason: string, content: array, text: string}
     */
    public function streamMessages(array $payload, callable $onToken): array;
}
