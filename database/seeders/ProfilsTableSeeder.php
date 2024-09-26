<?php

namespace Database\Seeders;
use App\Models\Profil;
use App\Models\AccessRight;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfilsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // liste des profils
        Profil::truncate();
        $roles = array(
            array(
                'name' => 'Super Administrateur',
                'code' => 'SUP_ADMIN'
            ),
            array(
                'name' => 'Administrateur',
                'code' => 'ADMIN'
            ),
            array(
                'name' => 'Advertiser',
                'code' => 'ADVERT'
            ),
            array(
                'name' => 'Gestionnaire',
                'code' => 'FACILIT'
            ),
        );

        /** Pour chaque profil crée un enregistrement dans la table profils */
        foreach ($roles as $role) {
          $existRole =DB::table('profils')->where('code', $role['code'])->first();
          if (!$existRole) {
            DB::table('profils')->insert($role);
          }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}
