<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\TranslateCategorie;

class TranslateCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Vider la table
        DB::table('translate_categories')->truncate();

        // Réactiver les contraintes
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        TranslateCategorie::create([
            "categorie_id"=>1,
            "title"=>"Consultation",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>2,
            "title"=>"Construction site",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>3,
            "title"=>"Vehicle",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>4,
            "title"=>"Stay",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>5,
            "title"=>"Events",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>6,
            "title"=>"Undeveloped land",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>7,
            "title"=>"Housing",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>8,
            "title"=>"Commercial space",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>9,
            "title"=>"Technician",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>10,
            "title"=>"Security",
        ]);

        TranslateCategorie::create([
            "categorie_id"=>11,
            "title"=>"Tourist site",
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }
}
