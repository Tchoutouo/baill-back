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
        
        // Vider la table
        DB::table('abonnements')->truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        
        Abonnement::create([
            "name"=>"Free",
            // "description"=>"L'annonce à une durée d'un mois",
            "time"=> 24,
            "type_time"=> "H",
            "price"=> 0,
            "type"=> "Free",
            "is_actived"=> 1,
            "hight_lite"=> true,
        ]);

        Abonnement::create([
            "name"=>"MENSUEL",
            // "description"=>"L'annonce est standard",
            "time"=> 30,
            "type_time"=> "S",
            "price"=> 20,
            "type"=> "Mensuel",
            "is_actived"=> 1,
            "hight_lite"=> true,
        ]);

        Abonnement::create([
            "name"=>"ANNUEL",
            // "description"=>"L'annonce est premium",
            "time"=> 12,
            "type_time"=> "M",
            "price"=> 10,
            "type"=> "Annuel",
            "is_actived"=> 1,
            "hight_lite"=> "1",
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
