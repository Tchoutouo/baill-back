<?php

namespace App\Agent;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AnthropicClient implements LlmClientInterface
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';
    private const VERSION = '2023-06-01';

    /** Appel synchrone — utilisé par AgentService::chat() */
    public function messages(array $payload): array
    {
        $payload['model'] = config('agent.anthropic_model', 'claude-haiku-4-5-20251001');

        $response = Http::timeout(30)
            ->withHeaders([
                'x-api-key'         => config('agent.anthropic_key'),
                'anthropic-version' => self::VERSION,
                'content-type'      => 'application/json',
            ])
            ->post(self::API_URL, $payload);

        if (!$response->successful()) {
            throw new RuntimeException(
                'Anthropic API error ' . $response->status() . ': ' . $response->body()
            );
        }

        return $response->json();
    }

    /**
     * Appel streaming (SSE) — utilisé par AgentService::stream().
     * Appelle $onToken(string $text) pour chaque token reçu.
     *
     * @return array{stop_reason: string, content: array, text: string}
     */
    public function streamMessages(array $payload, callable $onToken): array
    {
        $payload['stream'] = true;

        $guzzle   = new GuzzleClient();
        $response = $guzzle->request('POST', self::API_URL, [
            'headers' => [
                'x-api-key'         => config('agent.anthropic_key'),
                'anthropic-version' => self::VERSION,
                'content-type'      => 'application/json',
            ],
            'json'         => $payload,
            'stream'       => true,
            'timeout'      => 120,
            'read_timeout' => 120,
        ]);

        $body = $response->getBody();

        $buffer       = '';
        $currentEvent = '';
        $content      = [];
        $currentBlock = null;
        $currentInput = '';
        $currentText  = '';
        $stopReason   = 'end_turn';

        while (!$body->eof()) {
            $chunk  = $body->read(512);
            $buffer .= $chunk;

            while (($nlPos = strpos($buffer, "\n")) !== false) {
                $line   = rtrim(substr($buffer, 0, $nlPos), "\r");
                $buffer = substr($buffer, $nlPos + 1);

                if (str_starts_with($line, 'event: ')) {
                    $currentEvent = trim(substr($line, 7));

                } elseif (str_starts_with($line, 'data: ') && $currentEvent !== '') {
                    $raw  = trim(substr($line, 6));
                    $data = json_decode($raw, true);
                    if ($data === null) continue;

                    switch ($currentEvent) {
                        case 'content_block_start':
                            $currentBlock = $data['content_block'] ?? null;
                            $currentInput = '';
                            break;

                        case 'content_block_delta':
                            $delta = $data['delta'] ?? [];
                            if (($delta['type'] ?? '') === 'text_delta') {
                                $text         = $delta['text'] ?? '';
                                $currentText .= $text;
                                $onToken($text);
                            } elseif (($delta['type'] ?? '') === 'input_json_delta') {
                                $currentInput .= $delta['partial_json'] ?? '';
                            }
                            break;

                        case 'content_block_stop':
                            if ($currentBlock) {
                                if (($currentBlock['type'] ?? '') === 'text') {
                                    $content[]   = ['type' => 'text', 'text' => $currentText];
                                    $currentText = '';
                                } elseif (($currentBlock['type'] ?? '') === 'tool_use') {
                                    $content[] = [
                                        'type'  => 'tool_use',
                                        'id'    => $currentBlock['id'],
                                        'name'  => $currentBlock['name'],
                                        'input' => json_decode($currentInput, true) ?? [],
                                    ];
                                }
                                $currentBlock = null;
                            }
                            break;

                        case 'message_delta':
                            $stopReason = $data['delta']['stop_reason'] ?? 'end_turn';
                            break;
                    }
                }
            }
        }

        $fullText = collect($content)
            ->where('type', 'text')
            ->pluck('text')
            ->implode('');

        return [
            'stop_reason' => $stopReason,
            'content'     => $content,
            'text'        => $fullText,
        ];
    }
}
