<?php

namespace App\Agent;

use Illuminate\Support\Facades\Cache;

class ContextManager
{
    // Durée de vie d'une session conversationnelle (2 heures)
    private const TTL_SECONDS = 7200;

    // Nombre maximum de messages conservés en historique (pour maîtriser les tokens)
    private const MAX_MESSAGES = 20;

    public function getHistory(string $sessionId): array
    {
        return Cache::get($this->key($sessionId), []);
    }

    public function saveHistory(string $sessionId, array $messages): void
    {
        $trimmed = array_slice($messages, -self::MAX_MESSAGES);
        Cache::put($this->key($sessionId), $trimmed, self::TTL_SECONDS);
    }

    public function clearSession(string $sessionId): void
    {
        Cache::forget($this->key($sessionId));
    }

    private function key(string $sessionId): string
    {
        return 'agent_session_' . sha1($sessionId);
    }
}
