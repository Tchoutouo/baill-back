<?php

namespace Database\Seeders;

use App\Enums\ProfilCode;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'username'         => 'Super-admin',
                'first_name'       => 'Super-admin',
                'last_name'        => 'Super-admin',
                'email'            => 'superadmin@gmail.com',
                'whatsapp_number'  => '237678325388',
                'country'          => 'CM',
                'city'             => 'Yaoundé',
                'neighborhood'     => 'Melen',
                'password'         => Hash::make('superadmin'),
                'profil_id'        => ProfilCode::SuperAdmin->value,
            ],
            [
                'username'         => 'Owner bailleurnet',
                'first_name'       => 'Bioclean',
                'last_name'        => 'MOUMBE',
                'email'            => 'biocleanmoumbe@gmail.com',
                'whatsapp_number'  => '237694798186',
                'country'          => 'CM',
                'city'             => 'Yaoundé',
                'neighborhood'     => 'Coron',
                'password'         => Hash::make('superadmin'),
                'profil_id'        => ProfilCode::SuperAdmin->value,
            ],
            [
                'username'         => 'Admin',
                'first_name'       => 'Admin',
                'last_name'        => 'Admin',
                'email'            => 'admin@gmail.com',
                'whatsapp_number'  => '237698125857',
                'country'          => 'CM',
                'city'             => 'Yaoundé',
                'neighborhood'     => 'Cradat',
                'password'         => Hash::make('administrateur'),
                'profil_id'        => ProfilCode::Admin->value,
            ],
            [
                'username'         => 'Advertiser',
                'first_name'       => 'Advertiser',
                'last_name'        => 'Advertiser',
                'email'            => 'advertiser@gmail.com',
                'whatsapp_number'  => '237693124855',
                'country'          => 'CM',
                'city'             => 'Douala',
                'neighborhood'     => 'Deido',
                'password'         => Hash::make('advertiser'),
                'profil_id'        => ProfilCode::Advertiser->value,
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(['email' => $data['email']], $data);
        }
    }
}
