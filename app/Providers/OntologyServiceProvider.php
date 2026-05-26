<?php

namespace App\Providers;

use App\Agent\AgentService;
use App\Agent\AnthropicClient;
use App\Agent\ContextManager;
use App\Agent\LlmClientInterface;
use App\Agent\MistralClient;
use App\Agent\RoleGuard;
use App\Agent\ToolDispatcher;
use App\Agent\Tools\ApproveAnnonceTool;
use App\Agent\Tools\CreateAnnonceStepTool;
use App\Agent\Tools\GetAnnonceTool;
use App\Agent\Tools\GetAbonnementsTool;
use App\Agent\Tools\GetCategoriesTool;
use App\Agent\Tools\GetMyAnnoncesTool;
use App\Agent\Tools\GetPlatformStatsTool;
use App\Agent\Tools\ListPendingAnnoncesTool;
use App\Agent\Tools\RejectAnnonceTool;
use App\Agent\Tools\SearchAnnoncesTool;
use App\Agent\Tools\SearchUsersAdminTool;
use App\Agent\Tools\SubmitAnnonceTool;
use App\Agent\Tools\ValidateAnnonceDraftTool;
use App\Ontology\KnowledgeGraphService;
use App\Ontology\OntologyGenerator;
use App\Ontology\ShaclValidator;
use Illuminate\Console\Events\CommandFinished;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class OntologyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/agent.php', 'agent');

        // ── Couche Ontologie ─────────────────────────────────────────────────
        $this->app->singleton(OntologyGenerator::class);
        $this->app->singleton(KnowledgeGraphService::class);
        $this->app->singleton(ShaclValidator::class);

        // ── Couche Agent ─────────────────────────────────────────────────────
        // Factory : choisit le client LLM selon AGENT_PROVIDER (.env)
        $this->app->singleton(LlmClientInterface::class, function () {
            return config('agent.provider') === 'mistral'
                ? new MistralClient()
                : new AnthropicClient();
        });

        $this->app->singleton(ContextManager::class);
        $this->app->singleton(RoleGuard::class);

        $this->app->singleton(ToolDispatcher::class, function ($app) {
            $validator = $app->make(ShaclValidator::class);
            $kg        = $app->make(KnowledgeGraphService::class);

            $dispatcher = new ToolDispatcher($app->make(RoleGuard::class));
            $dispatcher->register(new SearchAnnoncesTool());
            $dispatcher->register(new GetAnnonceTool());
            $dispatcher->register(new GetCategoriesTool());
            $dispatcher->register(new GetAbonnementsTool());
            $dispatcher->register(new CreateAnnonceStepTool($validator));
            $dispatcher->register(new ValidateAnnonceDraftTool($validator));
            $dispatcher->register(new SubmitAnnonceTool($validator, $kg));
            $dispatcher->register(new GetMyAnnoncesTool());
            // Outils admin
            $dispatcher->register(new ListPendingAnnoncesTool());
            $dispatcher->register(new ApproveAnnonceTool($kg));
            $dispatcher->register(new RejectAnnonceTool());
            $dispatcher->register(new GetPlatformStatsTool());
            $dispatcher->register(new SearchUsersAdminTool());
            return $dispatcher;
        });

        // AgentService résout LlmClientInterface via le binding ci-dessus
        $this->app->singleton(AgentService::class);
    }

    public function boot(): void
    {
        // Regénère l'ontologie TTL après chaque migration
        Event::listen(CommandFinished::class, function (CommandFinished $event) {
            if (str_starts_with($event->command ?? '', 'migrate')) {
                Artisan::call('ontology:generate');
            }
        });
    }
}
