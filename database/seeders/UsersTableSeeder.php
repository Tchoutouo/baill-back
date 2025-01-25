<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
    //
    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
    
        /** Super-admin */
        User::create([
            "username"=>"Super-admin",
            "first_name"=>"Super-admin",
            "last_name"=>"Super-admin",
            "email"=>"superadmin@gmail.com",
            "whatsapp_number"=>"695124532",
            "country"=>"CM",
            "city"=>"Yaoundé",
            "neighborhood"=>"Melen",
            "password"=>Hash::make("superadmin"),
            "profil_id"=>"1",
        ]);

        User::create([
            "username"=>"Owner bailleurnet",
            "first_name"=>"Bioclean",
            "last_name"=>"MOUMBE",
            "email"=>"biocleanmoumbe@gmail.com",
            "whatsapp_number"=>"694798186",
            "country"=>"CM",
            "city"=>"Yaoundé",
            "neighborhood"=>"Coron",
            "password"=>Hash::make("superadmin"),
            "profil_id"=>"1",
        ]);
    
    /** Administrateur */
    User::create([
                "username"=>"Admin",
                "first_name"=>"Admin",
                "last_name"=>"Admin",
                "email"=>"admin@gmail.com",
                "whatsapp_number"=>"698125857",
                "country"=>"CM",
                "city"=>"Yaoundé",
                "neighborhood"=>"Cradat",
                "password"=>Hash::make("administrateur"),
                "profil_id"=>"2",
            ]);

    /** Advertiser */
    User::create([
                "username"=>"Advertiser",
                "last_name"=>"Advertiser",
                "first_name"=>"Advertiser",
                "email"=>"Advertiser@gmail.com",
                "whatsapp_number"=>"693124855",
                "country"=>"CM",
                "city"=>"Douala",
                "neighborhood"=>"Deido",
                "password"=>Hash::make("advertiser"),
                "profil_id"=>"3",
            ]);
    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
