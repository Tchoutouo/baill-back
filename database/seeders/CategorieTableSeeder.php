<?php

namespace Database\Seeders;

use App\Models\Categorie;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorieTableSeeder extends Seeder
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

        Categorie::create([
            "title"=>"Consultation",
            "description"=>"Médecine",
        ]);

        Categorie::create([
            "title"=>"Chantier",
            "description"=>"Construction",
        ]);

        Categorie::create([
            "title"=>"Véhicule",
            "description"=>"Transport",
        ]);

        Categorie::create([
            "title"=>"Séjour",
            "description"=>"Hôtel, motel etc...",
        ]);

        Categorie::create([
            "title"=>"Evènementiel",
            "description"=>"Mariage",
        ]);

        Categorie::create([
            "title"=>"Terrain non bâti",
            "description"=>"Terrain",
        ]);

        Categorie::create([
            "title"=>"Habitation",
            "description"=>"Maison, chambre",
        ]);

        Categorie::create([
            "title"=>"Espace commercial",
            "description"=>"Espace commercial",
        ]);

        Categorie::create([
            "title"=>"Technicien",
            "description"=>"Travaux",
        ]);

        Categorie::create([
            "title"=>"Sécurité",
            "description"=>"Travaux",
        ]);

        Categorie::create([
            "title"=>"Site touristique",
            "description"=>"Espace de loisir etc...",
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
