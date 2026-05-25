<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(ProfilsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CategorieTableSeeder::class);
        $this->call(AddVersionEnglishCategoriesTableSeeder::class);
        $this->call(AbonnementTableSeed::class);
        $this->call(ModePaiementSeed::class);
        $this->call(AnnonceTableSeeder::class);
    }
}
