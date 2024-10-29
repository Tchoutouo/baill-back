<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Abonnement;

class AbonnementTableSeed extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        
        Abonnement::create([
            "name"=>"Free",
            "description"=>"L'annonce à une durée d'un mois",
            "time"=> 30,
            "price"=> 0,
            "type"=> "Free",
            "is_actived"=> 1,
            "hight_lite"=> true,
        ]);

        Abonnement::create([
            "name"=>"Standard",
            "description"=>"L'annonce est standard",
            "time"=> 30,
            "price"=> 20,
            "type"=> "Standard",
            "is_actived"=> 1,
            "hight_lite"=> true,
        ]);

        Abonnement::create([
            "name"=>"Premium",
            "description"=>"L'annonce est premium",
            "time"=> 30,
            "price"=> 10,
            "type"=> "Premium",
            "is_actived"=> 1,
            "hight_lite"=> "1",
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
