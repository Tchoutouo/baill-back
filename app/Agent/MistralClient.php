<?php

namespace App\Agent;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Adaptateur Mistral AI — convertit le format normalisé interne (Anthropic-like)
 * vers le format OpenAI-compatible de Mistral, et la réponse dans l'autre sens.
 *
 * Format normalisé interne utilisé par AgentService :
 *   payload  : { system, max_tokens, tools (input_schema), messages }
 *   response : { stop_reason: 'end_turn'|'tool_use', content: [...blocks], text }
 */
class MistralClient implements LlmClientInterface
{
    private const API_URL = 'https://api.mistral.ai/v1/chat/completions';

    // ── API publique ──────────────────────────────────────────────────────────

    public function messages(array $payload): array
    {
        $mistralPayload = $this->toMistral($payload);

        $response = Http::timeout(30)
            ->withHeaders([
                'Authorization' => 'Bearer ' . config('agent.mistral_key'),
                'Content-Type'  => 'application/json',
            ])
            ->post(self::API_URL, $mistralPayload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Mistral API error ' . $response->status() . ': ' . $response->body()
            );
        }

        return $this->fromMistralResponse($response->json());
    }

    public function streamMessages(array $payload, callable $onToken): array
    {
        $mistralPayload           = $this->toMistral($payload);
        $mistralPayload['stream'] = true;

        $guzzle   = new GuzzleClient();
        $response = $guzzle->request('POST', self::API_URL, [
            'headers' => [
                'Authorization' => 'Bearer ' . config('agent.mistral_key'),
                'Content-Type'  => 'application/json',
            ],
            'json'         => $mistralPayload,
            'stream'       => true,
            'timeout'      => 120,
            'read_timeout' => 120,
        ]);

        $body           = $response->getBody();
        $buffer         = '';
        $textContent    = '';
        $toolCallChunks = [];   // indexés par index de tool_call
        $finishReason   = 'stop';

        while (!$body->eof()) {
            $buffer .= $body->read(512);

            while (($nlPos = strpos($buffer, "\n")) !== false) {
                $line   = rtrim(substr($buffer, 0, $nlPos), "\r");
                $buffer = substr($buffer, $nlPos + 1);

                if (!str_starts_with($line, 'data: ')) continue;

                $raw = trim(substr($line, 6));
                if ($raw === '[DONE]') break 2;

                $chunk = json_decode($raw, true);
                if (!$chunk) continue;

                $choice = $chunk['choices'][0] ?? null;
                if (!$choice) continue;

                if ($choice['finish_reason'] !== null) {
                    $finishReason = $choice['finish_reason'];
                }

                $delta = $choice['delta'] ?? [];

                // Token texte
                if (isset($delta['content']) && $delta['content'] !== '') {
                    $textContent .= $delta['content'];
                    $onToken($delta['content']);
                }

                // Fragments de tool_calls
                foreach ($delta['tool_calls'] ?? [] as $tcDelta) {
                    $idx = $tcDelta['index'] ?? 0;
                    if (!isset($toolCallChunks[$idx])) {
                        $toolCallChunks[$idx] = ['id' => '', 'name' => '', 'arguments' => ''];
                    }
                    if (!empty($tcDelta['id']))                           $toolCallChunks[$idx]['id']        = $tcDelta['id'];
                    if (!empty($tcDelta['function']['name']))              $toolCallChunks[$idx]['name']      .= $tcDelta['function']['name'];
                    if (isset($tcDelta['function']['arguments']))          $toolCallChunks[$idx]['arguments'] .= $tcDelta['function']['arguments'];
                }
            }
        }

        // Assemblage des blocs de contenu
        $content = [];
        if ($textContent !== '') {
            $content[] = ['type' => 'text', 'text' => $textContent];
        }
        ksort($toolCallChunks);
        foreach ($toolCallChunks as $tc) {
            $content[] = $this->toolCallBlock($tc['id'], $tc['name'], $tc['arguments']);
        }

        return [
            'stop_reason' => $this->normalizeStopReason($finishReason),
            'content'     => $content,
            'text'        => $textContent,
        ];
    }

    // ── Conversion payload → Mistral ─────────────────────────────────────────

    private function toMistral(array $payload): array
    {
        $messages = [];

        // Système → premier message role:system
        if (!empty($payload['system'])) {
            $messages[] = ['role' => 'system', 'content' => $payload['system']];
        }

        foreach ($payload['messages'] as $msg) {
            foreach ($this->convertMessage($msg) as $m) {
                $messages[] = $m;
            }
        }

        $result = [
            'model'      => config('agent.mistral_model', 'mistral-large-latest'),
            'max_tokens' => $payload['max_tokens'] ?? 1024,
            'messages'   => $messages,
        ];

        // Outils : input_schema → {type:function, function:{name,description,parameters}}
        if (!empty($payload['tools'])) {
            $result['tools']       = array_map(fn($t) => [
                'type'     => 'function',
                'function' => [
                    'name'        => $t['name'],
                    'description' => $t['description'],
                    'parameters'  => $t['input_schema'],
                ],
            ], $payload['tools']);
            $result['tool_choice'] = 'auto';
        }

        return $result;
    }

    /**
     * Convertit un message Anthropic-normalisé en 0..N messages Mistral.
     * Les tool_results (role=user, type=tool_result) deviennent des messages role=tool.
     */
    private function convertMessage(array $msg): array
    {
        $role    = $msg['role'];
        $content = $msg['content'];

        // Message texte simple
        if (is_string($content)) {
            return [['role' => $role, 'content' => $content]];
        }

        if (!is_array($content)) {
            return [];
        }

        // Messages utilisateur (peuvent contenir des tool_results)
        if ($role === 'user') {
            $toolResults = [];
            $textParts   = [];

            foreach ($content as $block) {
                $type = $block['type'] ?? '';
                if ($type === 'tool_result') {
                    $toolResults[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $block['tool_use_id'],
                        'content'      => is_string($block['content'])
                            ? $block['content']
                            : json_encode($block['content'], JSON_UNESCAPED_UNICODE),
                    ];
                } elseif ($type === 'text') {
                    $textParts[] = $block['text'];
                }
            }

            $result = [];
            if ($textParts) {
                $result[] = ['role' => 'user', 'content' => implode('', $textParts)];
            }
            return array_merge($result, $toolResults);
        }

        // Réponse assistant (peut contenir text + tool_use)
        if ($role === 'assistant') {
            $textParts = [];
            $toolCalls = [];

            foreach ($content as $block) {
                $type = $block['type'] ?? '';
                if ($type === 'text') {
                    $textParts[] = $block['text'];
                } elseif ($type === 'tool_use') {
                    $toolCalls[] = [
                        'id'       => $block['id'],
                        'type'     => 'function',
                        'function' => [
                            'name'      => $block['name'],
                            'arguments' => json_encode($block['input'] ?? [], JSON_UNESCAPED_UNICODE),
                        ],
                    ];
                }
            }

            $assistantMsg = ['role' => 'assistant', 'content' => implode('', $textParts) ?: ''];
            if ($toolCalls) {
                $assistantMsg['tool_calls'] = $toolCalls;
            }
            return [$assistantMsg];
        }

        return [['role' => $role, 'content' => '']];
    }

    // ── Conversion réponse Mistral → format normalisé ────────────────────────

    private function fromMistralResponse(array $response): array
    {
        $choice  = $response['choices'][0] ?? [];
        $message = $choice['message'] ?? [];
        $finish  = $choice['finish_reason'] ?? 'stop';

        $content = [];

        if (!empty($message['content'])) {
            $content[] = ['type' => 'text', 'text' => $message['content']];
        }

        foreach ($message['tool_calls'] ?? [] as $tc) {
            $content[] = $this->toolCallBlock(
                $tc['id'],
                $tc['function']['name'],
                $tc['function']['arguments'] ?? '{}',
            );
        }

        $text = collect($content)->where('type', 'text')->pluck('text')->implode('');

        return [
            'stop_reason' => $this->normalizeStopReason($finish),
            'content'     => $content,
            'text'        => $text,
        ];
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    private function toolCallBlock(string $id, string $name, string $argumentsJson): array
    {
        return [
            'type'  => 'tool_use',
            'id'    => $id,
            'name'  => $name,
            'input' => json_decode($argumentsJson, true) ?? [],
        ];
    }

    private function normalizeStopReason(string $mistralReason): string
    {
        return $mistralReason === 'tool_calls' ? 'tool_use' : 'end_turn';
    }
}
