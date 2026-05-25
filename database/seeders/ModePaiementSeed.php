<?php

namespace Database\Seeders;

use App\Models\ModePaiement;
use Illuminate\Database\Seeder;

class ModePaiementSeed extends Seeder
{
    public function run(): void
    {
        $modes = [
            ['title' => 'Stripe',       'code' => 'stripe', 'is_active' => 1],
            ['title' => 'Orange Money', 'code' => 'om',     'is_active' => 0],
            ['title' => 'Mobile Money', 'code' => 'momo',   'is_active' => 0],
        ];

        foreach ($modes as $data) {
            ModePaiement::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
