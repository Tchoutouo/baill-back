<?php

namespace Database\Seeders;

use App\Models\ModePaiement;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModePaiementSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        ModePaiement::create([
            "title"=>"Stripe",
            "is_active"=> 1,
        ]);

        ModePaiement::create([
            "title"=>"Orange Money",
            "is_active"=> 0,
        ]);

        ModePaiement::create([
            "title"=>"Mobile Money",
            "is_active"=> 0,
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}

