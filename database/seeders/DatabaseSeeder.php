<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Categorie;
use Illuminate\Database\Seeder;
use Database\Seeders\ProfilsTableSeeder;
use Database\Seeders\UsersTableSeeder;
use Database\Seeders\CategorieTableSeeder;
use Database\Seeders\AbonnementTableSeed;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProfilsTableSeeder::class);
        $this->call(UsersTableSeeder::class);
        $this->call(CategorieTableSeeder::class);
        $this->call(AbonnementTableSeed::class);
    }
}
