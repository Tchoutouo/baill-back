<?php

namespace App\Http\Controllers;

use App\Agent\AgentService;
use App\Agent\RoleGuard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AgentController extends Controller
{
    public function __construct(
        private readonly AgentService $agent,
        private readonly RoleGuard    $roleGuard,
    ) {}

    /**
     * POST /v1/agent/chat
     *
     * Body: { message: string, session_id?: string, locale?: string }
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:1000'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ]);

        $sessionId = $request->input('session_id') ?: Str::uuid()->toString();
        $user      = Auth::guard('sanctum')->user();
        $userRole  = $this->roleGuard->roleFromUser($user);

        // Préfixe le session_id par l'user id si connecté (isolation des sessions)
        if ($user) {
            $sessionId = 'u' . $user->id . '_' . $sessionId;
        }

        $result = $this->agent->chat(
            message:   $request->input('message'),
            sessionId: $sessionId,
            userRole:  $userRole,
            user:      $user,
        );

        return response()->json($result);
    }

    /**
     * POST /v1/agent/stream
     * Même logique que chat() mais la réponse est envoyée en Server-Sent Events.
     */
    public function stream(Request $request): StreamedResponse
    {
        $request->validate([
            'message'    => ['required', 'string', 'max:1000'],
            'session_id' => ['nullable', 'string', 'max:64'],
        ]);

        $sessionId = $request->input('session_id') ?: Str::uuid()->toString();
        $user      = Auth::guard('sanctum')->user();
        $userRole  = $this->roleGuard->roleFromUser($user);

        if ($user) {
            $sessionId = 'u' . $user->id . '_' . $sessionId;
        }

        $message = $request->input('message');

        return response()->stream(
            function () use ($message, $sessionId, $userRole, $user): void {
                // Vide les buffers d'output PHP pour que flush() soit immédiat
                while (ob_get_level() > 0) {
                    ob_end_clean();
                }
                ob_implicit_flush(true);

                $this->agent->stream(
                    $message, $sessionId, $userRole, $user,
                    function (array $event): void {
                        echo "event: {$event['event']}\n";
                        echo 'data: ' . json_encode($event['data'], JSON_UNESCAPED_UNICODE) . "\n\n";
                        flush();
                    }
                );
            },
            200,
            [
                'Content-Type'      => 'text/event-stream; charset=UTF-8',
                'Cache-Control'     => 'no-cache, no-store',
                'X-Accel-Buffering' => 'no',
                'Connection'        => 'keep-alive',
            ]
        );
    }

    /**
     * DELETE /v1/agent/session
     * Efface l'historique de conversation du client.
     */
    public function clearSession(Request $request): JsonResponse
    {
        $sessionId = $request->input('session_id');
        if ($sessionId) {
            $user = Auth::guard('sanctum')->user();
            if ($user) {
                $sessionId = 'u' . $user->id . '_' . $sessionId;
            }
            $this->agent->clearSession($sessionId);
        }

        return response()->json(['cleared' => true]);
    }
}
