<?php

namespace App\Agent;

use Throwable;

class AgentService
{
    private const MAX_ITERATIONS = 5;

    public function __construct(
        private readonly LlmClientInterface $anthropic,
        private readonly ContextManager     $context,
        private readonly ToolDispatcher     $dispatcher,
        private readonly RoleGuard          $roleGuard,
    ) {}

    // ── API publique ──────────────────────────────────────────────────────────

    /**
     * Réponse synchrone complète (utilisé en fallback ou tests).
     *
     * @return array{session_id: string, message: string, data: array|null}
     */
    public function chat(string $message, string $sessionId, string $userRole = 'visitor', mixed $user = null): array
    {
        [$history, $messages, $context, $toolDefs] = $this->prepareSession($message, $sessionId, $userRole, $user);

        $collectedData = [];
        $finalText     = null;
        $toolCache     = [];

        try {
            for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
                $apiResponse = $this->anthropic->messages(
                    $this->buildPayload($userRole, $user, $toolDefs, $messages)
                );

                $stopReason = $apiResponse['stop_reason'] ?? 'end_turn';
                $content    = $apiResponse['content'] ?? [];

                if ($stopReason === 'end_turn') {
                    $finalText = collect($content)->where('type', 'text')->pluck('text')->implode('');
                    break;
                }

                if ($stopReason === 'tool_use') {
                    $messages = $this->executeTools(
                        $content, $messages, $userRole, $context, $collectedData, $toolCache
                    );
                }
            }
        } catch (Throwable $e) {
            report($e);
            $finalText = "Je rencontre une difficulté technique. Veuillez réessayer dans quelques instants.";
        }

        $finalText ??= "Je n'ai pas pu traiter votre demande. Pouvez-vous reformuler ?";
        if ($finalText === '') {
            $finalText = "Je n'ai pas pu traiter votre demande. Pouvez-vous reformuler ?";
        }

        $this->context->saveHistory($sessionId, [...$history, ['role' => 'assistant', 'content' => $finalText]]);

        return [
            'session_id' => $sessionId,
            'message'    => $finalText,
            'data'       => $collectedData ?: null,
        ];
    }

    /**
     * Réponse en streaming SSE.
     * $emit est appelée avec ['event' => string, 'data' => array] pour chaque événement SSE.
     */
    public function stream(
        string   $message,
        string   $sessionId,
        string   $userRole,
        mixed    $user,
        callable $emit,
    ): void {
        [$history, $messages, $context, $toolDefs] = $this->prepareSession($message, $sessionId, $userRole, $user);

        $collectedData = [];
        $finalText     = '';
        $toolCache     = [];

        try {
            for ($i = 0; $i < self::MAX_ITERATIONS; $i++) {
                $assembled = $this->anthropic->streamMessages(
                    $this->buildPayload($userRole, $user, $toolDefs, $messages),
                    fn(string $token) => $emit(['event' => 'token', 'data' => ['text' => $token]])
                );

                $stopReason = $assembled['stop_reason'];

                if ($stopReason === 'end_turn') {
                    $finalText = $assembled['text'];
                    break;
                }

                if ($stopReason === 'tool_use') {
                    foreach (collect($assembled['content'])->where('type', 'tool_use') as $block) {
                        $emit(['event' => 'tool', 'data' => ['name' => $block['name']]]);
                    }

                    $messages = $this->executeTools(
                        $assembled['content'], $messages, $userRole, $context, $collectedData, $toolCache
                    );
                }
            }
        } catch (Throwable $e) {
            report($e);
            $emit(['event' => 'error', 'data' => ['message' => 'Erreur technique. Veuillez réessayer.']]);
            return;
        }

        if ($finalText === '') {
            $finalText = "Je n'ai pas pu traiter votre demande. Pouvez-vous reformuler ?";
            $emit(['event' => 'token', 'data' => ['text' => $finalText]]);
        }

        $this->context->saveHistory($sessionId, [...$history, ['role' => 'assistant', 'content' => $finalText]]);

        if (!empty($collectedData)) {
            $emit(['event' => 'data', 'data' => $collectedData]);
        }

        $emit(['event' => 'done', 'data' => ['session_id' => $sessionId]]);
    }

    public function clearSession(string $sessionId): void
    {
        $this->context->clearSession($sessionId);
    }

    // ── Helpers partagés ─────────────────────────────────────────────────────

    /** Initialise le contexte commun aux deux modes (chat / stream). */
    private function prepareSession(
        string $message,
        string $sessionId,
        string $userRole,
        mixed  $user,
    ): array {
        $history   = $this->context->getHistory($sessionId);
        $history[] = ['role' => 'user', 'content' => $message];

        $context      = $user ? ['user_id' => $user->id, 'user' => $user] : [];
        $allowedTools = $this->roleGuard->toolsFor($userRole);
        $toolDefs     = $this->dispatcher->definitions($allowedTools);

        return [$history, $history, $context, $toolDefs];
    }

    private function buildPayload(string $userRole, mixed $user, array $toolDefs, array $messages): array
    {
        return [
            'max_tokens' => config('agent.max_tokens', 1024),
            'system'     => $this->systemPrompt($userRole, $user),
            'tools'      => $toolDefs,
            'messages'   => $messages,
        ];
    }

    /**
     * Exécute les tool_use blocks, accumule les données et retourne les messages mis à jour.
     * $toolCache évite les appels DB redondants au sein d'une même requête ReAct.
     */
    private function executeTools(
        array  $content,
        array  $messages,
        string $userRole,
        array  $context,
        array  &$collectedData,
        array  &$toolCache,
    ): array {
        $toolUseBlocks = collect($content)->where('type', 'tool_use');
        $messages[]    = ['role' => 'assistant', 'content' => $content];

        $toolResults = [];
        foreach ($toolUseBlocks as $block) {
            $cacheKey = $block['name'] . ':' . md5(json_encode($block['input'] ?? []));

            if (!isset($toolCache[$cacheKey])) {
                $toolCache[$cacheKey] = $this->dispatcher->execute(
                    $block['name'],
                    $block['input'] ?? [],
                    $userRole,
                    $context,
                );
            }

            $result = $toolCache[$cacheKey];

            $toolResults[] = [
                'type'        => 'tool_result',
                'tool_use_id' => $block['id'],
                'content'     => json_encode($result, JSON_UNESCAPED_UNICODE),
            ];

            $this->mergeData($collectedData, $result);
        }

        $messages[] = ['role' => 'user', 'content' => $toolResults];
        return $messages;
    }

    // ── Prompt système ────────────────────────────────────────────────────────

    private function systemPrompt(string $role, mixed $user = null): string
    {
        $username = $user?->username ?? $user?->name ?? null;
        $greeting = $username ? "L'annonceur connecté se nomme **{$username}**." : '';

        $roleContext = match ($role) {
            'advertiser' => <<<ROLE

L'utilisateur est un annonceur connecté. {$greeting}
En plus de la recherche, tu peux l'aider à créer et gérer ses annonces.

WORKFLOW DE CRÉATION D'ANNONCE :
1. Recueille les informations étape par étape en appelant `create_annonce_step` à chaque réponse de l'utilisateur
2. Commence toujours par demander le titre, puis le prix, la localisation et le contact
3. Propose les catégories disponibles avec `get_categories` si l'utilisateur ne sait pas
4. Propose les forfaits avec `get_abonnements` avant de demander `abonnement_id`
5. Appelle `validate_annonce_draft` quand tu penses que le brouillon est complet
6. Ne soumet JAMAIS avec `submit_annonce` sans confirmation explicite de l'utilisateur
7. Après soumission réussie, annonce la référence et explique que l'annonce est en attente de validation
ROLE,
            'admin' => <<<ROLE

L'utilisateur est un administrateur de bailleurnet.cm. Tu as accès à tous les outils.

WORKFLOW DE MODÉRATION :
1. Pour lister les annonces en attente : `list_pending_annonces`
2. Pour approuver une annonce : confirme d'abord avec l'admin, puis appelle `approve_annonce`
3. Pour rejeter une annonce : confirme d'abord avec l'admin (demande le motif), puis appelle `reject_annonce`
4. Pour les statistiques globales : `get_platform_stats`
5. Pour rechercher un utilisateur : `search_users_admin`

RÈGLES ADMIN :
- Demande toujours une confirmation explicite avant toute action d'approbation ou de rejet
- Ne rejette jamais une annonce sans proposer à l'admin de préciser un motif
- Lors d'une approbation, annonce la date d'expiration calculée
ROLE,
            default => '',
        };

        return <<<PROMPT
        Tu es l'assistant de bailleurnet.cm, plateforme d'annonces immobilières au Cameroun.

        TON RÔLE :
        - Aider les visiteurs à trouver des annonces correspondant à leurs besoins
        - Répondre aux questions sur le fonctionnement de la plateforme
        - Orienter vers les catégories et forfaits disponibles
        - Fournir des liens directs vers les annonces sous la forme /annonce/{id}{$roleContext}

        RÈGLES STRICTES :
        1. Tu ne réponds QU'aux questions liées à l'immobilier et à bailleurnet.cm
        2. Si une question est hors sujet, réponds exactement : "Je suis spécialisé dans les annonces immobilières sur bailleurnet.cm. Pour toute autre question, contactez notre support."
        3. Ne génère jamais d'informations sur des annonces qui ne sont pas dans tes données
        4. Ne génère jamais de prix, contacts ou adresses inventés
        5. Réponds toujours en français, sauf si l'utilisateur écrit en anglais

        FORMAT DE RÉPONSE :
        - Sois concis et direct
        - Quand tu présentes des annonces, liste-les avec leur prix et lien
        - Si aucune annonce ne correspond, dis-le clairement et propose des alternatives
        PROMPT;
    }

    // ── Fusion des données collectées ─────────────────────────────────────────

    private function mergeData(array &$collected, array $result): void
    {
        foreach (['annonces', 'categories', 'abonnements'] as $key) {
            if (!empty($result[$key])) {
                $collected[$key] = array_merge($collected[$key] ?? [], $result[$key]);
            }
        }

        if (!empty($result['id']) && !empty($result['title']) && !isset($result['draft'])) {
            $collected['annonce'] = $result;
        }

        if (isset($result['draft'])) {
            $collected['draft'] = [
                'fields'         => $result['draft'],
                'missing_fields' => $result['missing_fields'] ?? [],
                'is_complete'    => $result['is_complete'] ?? false,
            ];
        }

        if (!empty($result['submission'])) {
            $collected['submission'] = $result['submission'];
        }
    }
}
