<?php

namespace App\Console\Commands;

use App\Models\Annonce;
use Illuminate\Console\Command;

class ExpireAnnonces extends Command
{
    protected $signature   = 'annonces:expire';
    protected $description = 'Passe en statut expiré (0) les annonces dont la date d\'expiration est dépassée';

    public function handle(): int
    {
        $count = Annonce::whereNotNull('expiration_date')
            ->where('expiration_date', '<', now()->toDateString())
            ->where('status', '!=', '0')
            ->update(['status' => '0']);

        $this->info("$count annonce(s) expirée(s).");

        return self::SUCCESS;
    }
}
