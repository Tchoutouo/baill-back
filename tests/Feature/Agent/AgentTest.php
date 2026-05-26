<?php

namespace Tests\Feature\Agent;

use App\Agent\LlmClientInterface;
use App\Agent\RoleGuard;
use App\Agent\ToolDispatcher;
use App\Enums\ProfilCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AgentTest extends TestCase
{
    use RefreshDatabase;

    private const CHAT_URL   = '/api/v1/agent/chat';
    private const STREAM_URL = '/api/v1/agent/stream';

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('profils')->insertOrIgnore([
            ['id' => 1, 'name' => 'Super Administrateur', 'code' => 'SUP_ADMIN'],
            ['id' => 2, 'name' => 'Administrateur',       'code' => 'ADMIN'],
            ['id' => 3, 'name' => 'Advertiser',           'code' => 'ADVERT'],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** Bind a mock LLM client that returns a simple text response. */
    private function mockLlm(string $text = 'Bonjour ! Comment puis-je vous aider ?'): void
    {
        $mock = $this->createMock(LlmClientInterface::class);

        $mock->method('messages')->willReturn([
            'stop_reason' => 'end_turn',
            'content'     => [['type' => 'text', 'text' => $text]],
            'text'        => $text,
        ]);

        $mock->method('streamMessages')->willReturnCallback(function (array $_payload, callable $onToken) use ($text): array {
            foreach (str_split($text, 5) as $chunk) {
                $onToken($chunk);
            }
            return [
                'stop_reason' => 'end_turn',
                'content'     => [['type' => 'text', 'text' => $text]],
                'text'        => $text,
            ];
        });

        $this->app->instance(LlmClientInterface::class, $mock);
    }

    // ── Chat endpoint — basics ─────────────────────────────────────────────────

    public function test_visitor_can_chat_without_authentication(): void
    {
        $this->mockLlm('Voici les annonces disponibles.');

        $response = $this->postJson(self::CHAT_URL, [
            'message' => 'Je cherche un appartement à Yaoundé',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['session_id', 'message', 'data'])
                 ->assertJsonPath('message', 'Voici les annonces disponibles.');
    }

    public function test_chat_requires_message(): void
    {
        $response = $this->postJson(self::CHAT_URL, []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_rejects_message_over_1000_chars(): void
    {
        $response = $this->postJson(self::CHAT_URL, [
            'message' => str_repeat('a', 1001),
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['message']);
    }

    public function test_chat_returns_session_id(): void
    {
        $this->mockLlm();

        $response = $this->postJson(self::CHAT_URL, [
            'message'    => 'Bonjour',
            'session_id' => 'my-session-123',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('session_id', 'my-session-123');
    }

    public function test_authenticated_user_session_is_prefixed(): void
    {
        $this->mockLlm();

        $user = User::factory()->create([
            'profil_id' => ProfilCode::Advertiser->value,
            'status'    => true,
        ]);

        $response = $this->actingAs($user, 'sanctum')
                         ->postJson(self::CHAT_URL, [
                             'message'    => 'Bonjour',
                             'session_id' => 'abc',
                         ]);

        $response->assertStatus(200)
                 ->assertJsonPath('session_id', 'u' . $user->id . '_abc');
    }

    // ── Role guard ────────────────────────────────────────────────────────────

    public function test_role_guard_visitor_tools(): void
    {
        $guard    = app(RoleGuard::class);
        $tools    = $guard->toolsFor('visitor');

        $this->assertContains('search_annonces', $tools);
        $this->assertContains('get_annonce_details', $tools);
        $this->assertNotContains('submit_annonce', $tools);
        $this->assertNotContains('approve_annonce', $tools);
    }

    public function test_role_guard_advertiser_tools(): void
    {
        $guard = app(RoleGuard::class);
        $tools = $guard->toolsFor('advertiser');

        $this->assertContains('create_annonce_step', $tools);
        $this->assertContains('submit_annonce', $tools);
        $this->assertNotContains('approve_annonce', $tools);
        $this->assertNotContains('list_pending_annonces', $tools);
    }

    public function test_role_guard_admin_has_wildcard(): void
    {
        $guard = app(RoleGuard::class);
        $tools = $guard->toolsFor('admin');

        $this->assertContains('*', $tools);
        $this->assertTrue($guard->canUse('admin', 'approve_annonce'));
        $this->assertTrue($guard->canUse('admin', 'any_future_tool'));
    }

    public function test_role_guard_blocks_unauthorized_tool(): void
    {
        $guard = app(RoleGuard::class);

        $this->assertFalse($guard->canUse('visitor', 'submit_annonce'));
        $this->assertFalse($guard->canUse('advertiser', 'approve_annonce'));
    }

    public function test_role_from_user_admin(): void
    {
        $guard = app(RoleGuard::class);

        $admin = User::factory()->create([
            'profil_id' => ProfilCode::Admin->value,
            'status'    => true,
        ]);
        $admin->load('profils');

        $this->assertEquals('admin', $guard->roleFromUser($admin));
    }

    public function test_role_from_user_advertiser(): void
    {
        $guard = app(RoleGuard::class);

        $user = User::factory()->create([
            'profil_id' => ProfilCode::Advertiser->value,
            'status'    => true,
        ]);
        $user->load('profils');

        $this->assertEquals('advertiser', $guard->roleFromUser($user));
    }

    public function test_role_from_user_null_returns_visitor(): void
    {
        $guard = app(RoleGuard::class);
        $this->assertEquals('visitor', $guard->roleFromUser(null));
    }

    // ── ToolDispatcher — role enforcement ────────────────────────────────────

    public function test_dispatcher_returns_error_for_unauthorized_tool(): void
    {
        $dispatcher = app(ToolDispatcher::class);

        $result = $dispatcher->execute('submit_annonce', [], 'visitor', []);

        $this->assertArrayHasKey('error', $result);
        $this->assertStringContainsString('submit_annonce', $result['error']);
    }

    public function test_dispatcher_throws_for_unknown_tool(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/Outil inconnu/');

        app(ToolDispatcher::class)->execute('nonexistent_tool', [], 'admin', []);
    }

    public function test_dispatcher_definitions_filter_by_role(): void
    {
        $dispatcher = app(ToolDispatcher::class);
        $guard      = app(RoleGuard::class);

        $visitorDefs    = $dispatcher->definitions($guard->toolsFor('visitor'));
        $advertiserDefs = $dispatcher->definitions($guard->toolsFor('advertiser'));

        $visitorNames    = array_column($visitorDefs, 'name');
        $advertiserNames = array_column($advertiserDefs, 'name');

        $this->assertContains('search_annonces', $visitorNames);
        $this->assertNotContains('submit_annonce', $visitorNames);
        $this->assertContains('submit_annonce', $advertiserNames);
        $this->assertNotContains('approve_annonce', $advertiserNames);
    }

    public function test_dispatcher_admin_definitions_include_all_tools(): void
    {
        $dispatcher = app(ToolDispatcher::class);
        $guard      = app(RoleGuard::class);

        $adminDefs = $dispatcher->definitions($guard->toolsFor('admin'));
        $names     = array_column($adminDefs, 'name');

        foreach ([
            'search_annonces', 'submit_annonce',
            'list_pending_annonces', 'approve_annonce', 'reject_annonce',
            'get_platform_stats', 'search_users_admin',
        ] as $tool) {
            $this->assertContains($tool, $names, "Admin should have tool: {$tool}");
        }
    }

    // ── Clear session ─────────────────────────────────────────────────────────

    public function test_clear_session_returns_success(): void
    {
        $response = $this->deleteJson('/api/v1/agent/session', [
            'session_id' => 'test-session-xyz',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('cleared', true);
    }

    // ── LLM provider config ───────────────────────────────────────────────────

    public function test_anthropic_is_default_provider(): void
    {
        $this->assertEquals('anthropic', config('agent.provider'));
    }

    public function test_mistral_client_is_bound_when_provider_is_mistral(): void
    {
        config(['agent.provider' => 'mistral']);

        // Rebind using the same factory logic as OntologyServiceProvider
        $this->app->singleton(LlmClientInterface::class, function () {
            return config('agent.provider') === 'mistral'
                ? new \App\Agent\MistralClient()
                : new \App\Agent\AnthropicClient();
        });

        $client = app(LlmClientInterface::class);
        $this->assertInstanceOf(\App\Agent\MistralClient::class, $client);
    }

    public function test_anthropic_client_is_bound_when_provider_is_anthropic(): void
    {
        config(['agent.provider' => 'anthropic']);

        $this->app->singleton(LlmClientInterface::class, function () {
            return config('agent.provider') === 'mistral'
                ? new \App\Agent\MistralClient()
                : new \App\Agent\AnthropicClient();
        });

        $client = app(LlmClientInterface::class);
        $this->assertInstanceOf(\App\Agent\AnthropicClient::class, $client);
    }

    // ── Tool caching within ReAct loop ────────────────────────────────────────

    public function test_tool_cache_deduplicates_calls_in_react_loop(): void
    {
        $mock = $this->createMock(LlmClientInterface::class);

        // First call: tool_use; second call: end_turn
        $mock->method('messages')
             ->willReturnOnConsecutiveCalls(
                 [
                     'stop_reason' => 'tool_use',
                     'content'     => [
                         ['type' => 'text',     'text' => ''],
                         ['type' => 'tool_use', 'id' => 'tu_1', 'name' => 'get_categories', 'input' => []],
                         ['type' => 'tool_use', 'id' => 'tu_2', 'name' => 'get_categories', 'input' => []], // duplicate
                     ],
                 ],
                 [
                     'stop_reason' => 'end_turn',
                     'content'     => [['type' => 'text', 'text' => 'Voici les catégories.']],
                     'text'        => 'Voici les catégories.',
                 ]
             );

        $this->app->instance(LlmClientInterface::class, $mock);

        // Intercept ToolDispatcher to count actual executions
        $originalDispatcher = app(ToolDispatcher::class);
        $dispatcherSpy = $this->getMockBuilder(ToolDispatcher::class)
                              ->setConstructorArgs([app(RoleGuard::class)])
                              ->onlyMethods(['execute'])
                              ->getMock();

        // Safe in tests: we own both objects and need to copy private state.
        $ref      = new \ReflectionProperty(ToolDispatcher::class, 'registry'); // NOSONAR
        $registry = $ref->getValue($originalDispatcher);                        // NOSONAR
        $ref->setValue($dispatcherSpy, $registry);

        $dispatcherSpy->expects($this->once()) // called once despite two identical blocks
                      ->method('execute')
                      ->with('get_categories', [], 'visitor', [])
                      ->willReturn(['categories' => []]);

        $this->app->instance(ToolDispatcher::class, $dispatcherSpy);

        $response = $this->postJson(self::CHAT_URL, [
            'message' => 'Quelles sont les catégories ?',
        ]);

        $response->assertStatus(200);
    }

    // ── Stream endpoint ───────────────────────────────────────────────────────

    public function test_stream_route_returns_streamed_response(): void
    {
        $this->withoutExceptionHandling();
        // In Laravel 10 the StreamedResponse closure is never called by the test
        // framework, so we only verify the route resolves and returns the right
        // response type — no LLM mock needed.
        $response = $this->postJson(self::STREAM_URL, ['message' => 'Bonjour']);

        $response->assertStatus(200);
        $this->assertInstanceOf(
            \Symfony\Component\HttpFoundation\StreamedResponse::class,
            $response->baseResponse
        );
    }

    public function test_stream_emits_token_and_done_events(): void
    {
        $text = 'Bonjour !';
        $this->mockLlm($text);
        $this->app->forgetInstance(\App\Agent\AgentService::class);

        $events = [];
        app(\App\Agent\AgentService::class)->stream(
            'Bonjour',
            'test-session-stream',
            'visitor',
            null,
            function (array $event) use (&$events): void { $events[] = $event; }
        );

        $types = array_column($events, 'event');
        $this->assertContains('token', $types, 'Expected at least one token event');
        $this->assertContains('done', $types, 'Expected a done event');

        $tokenText = implode('', array_map(
            fn($e) => $e['data']['text'] ?? '',
            array_filter($events, fn($e) => $e['event'] === 'token')
        ));
        $this->assertStringContainsString('Bonjour', $tokenText);
    }

    public function test_stream_requires_message(): void
    {
        $response = $this->postJson(self::STREAM_URL, []);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['message']);
    }
}
