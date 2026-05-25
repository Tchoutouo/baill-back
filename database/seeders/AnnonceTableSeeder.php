<?php

namespace Database\Seeders;

use App\Models\Annonce;
use App\Models\User;
use Illuminate\Database\Seeder;

class AnnonceTableSeeder extends Seeder
{
    public function run(): void
    {
        if (Annonce::exists()) {
            return;
        }

        $advertiser = User::where('email', 'advertiser@gmail.com')->first();
        if (!$advertiser) {
            return;
        }

        for ($i = 1; $i <= 50; $i++) {
            Annonce::create([
                'title'          => 'Chambre à louer ' . $i,
                'subtitle'       => 'Porte ' . $i,
                'description'    => 'Description de la chambre ' . $i,
                'reference'      => 'ANN ' . $i,
                'price'          => rand(2000, 5000) + (rand(0, 99) / 100),
                'contact'        => '693124' . rand(1000, 9999),
                'country'        => 'Cameroun',
                'neighborhood'   => 'Melen',
                'status'         => 3,
                'is_forward'     => 1,
                'abonnement_id'  => 1,
                'user_id'        => $advertiser->id,
            ]);
        }
    }
}
