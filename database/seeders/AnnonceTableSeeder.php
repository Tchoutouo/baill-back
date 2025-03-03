<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Annonce;

class AnnonceTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        for ($i = 0; $i < 50; $i++) {
            // Vous pouvez dupliquer le même enregistrement ou modifier certains champs
            $annonceData = [
                "title" => "Chambre à louer " . ($i + 1),
                "subtitle" => "Porte " . ($i + 1),
                "description" => "Description de la chambre " . ($i + 1),
                "reference" => "ANN " . ($i + 1),
                "price" => rand(2000, 5000) + (rand(0, 99) / 100), // Prix aléatoire entre 2000 et 5000
                "contact" => "693124" . rand(1000, 9999), // Numéro de contact aléatoire
                "country" => "Cameroun",
                "neighborhood" => "Melen",
                // "is_published" => 1,
                // "images" => json_encode(["apropos1.PNG", "balafong.jpg"]),
                "status" => 3,
                "is_forward" => 1,
                // "categorie" => json_encode(["1"]),
                "abonnement_id" => "1",
                "user_id" => "3"
            ];

            // Insérer l'annonce dans la base de données
            Annonce::create($annonceData);
        } // Créer 50 enregistrements d'annonces en utilisant la factory
            
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
