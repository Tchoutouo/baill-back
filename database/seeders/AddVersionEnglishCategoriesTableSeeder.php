<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Categorie;

class AddVersionEnglishCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        try {
            $categories = Categorie::get();
    
            if(!empty($categories))
            {
                foreach ($categories as $categorie) {
                    if (!$categorie->title_en) {
                        if ($categorie->title === "Consultation") {
                            $categorie->title_en = "Consultation";
                        }
                        if($categorie->title === "Chantier") {
                            $categorie->title_en = "Constructor";
                        }
                        if ($categorie->title === "Véhicule") {
                            $categorie->title_en = "Vehicle";
                        }
                        if ($categorie->title === "Séjour") {
                            $categorie->title_en = "Stay";
                        }
                        if ($categorie->title === "Evènementiel") {
                            $categorie->title_en = "Events";
                        }
                        if ($categorie->title === "Terrain agricole") {
                            $categorie->title_en = "Agricultural land";
                        }
                        if ($categorie->title === "Habitation") {
                            $categorie->title_en = "House";
                        }
                        if ($categorie->title === "Bureaux et boutiques") {
                            $categorie->title_en = "Offices and shops";
                        }
                        if ($categorie->title === "Technicien") {
                            $categorie->title_en = "Technician";
                        }
                        if ($categorie->title === "Sécurité") {
                            $categorie->title_en = "Security";
                        }
                        if ($categorie->title === "Site touristique") {
                            $categorie->title_en = "Tourist resort";
                        }
                        if ($categorie->title === "Colis et courrier") {
                            $categorie->title_en = "Parcels and mail";
                        }
                        if ($categorie->title === "Transport urbain") {
                            $categorie->title_en = "Urban transport";
                        }
                        if ($categorie->title === "SOS Boutiquier") {
                            $categorie->title_en = "SOS Shopkeeper";
                        }
                        if ($categorie->title === "Billet d'avions") {
                            $categorie->title_en = "Plane ticket";
                        }
                        if ($categorie->title === "Assurance") {
                            $categorie->title_en = "Insurance";
                        }
                    }
    
                    $categorie->save();
                }
            }
        } catch (\Exception $e) {
             Log::error('Erreur lors de l\'exécution du seed de traduction des catégories: ' . $e->getMessage());
        }
    }
}
