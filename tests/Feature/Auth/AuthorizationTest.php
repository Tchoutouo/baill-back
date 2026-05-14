<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\ProfilCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        DB::table('profils')->insertOrIgnore([
            ['id' => 1, 'name' => 'Super Administrateur', 'code' => 'SUP_ADMIN'],
            ['id' => 2, 'name' => 'Administrateur',       'code' => 'ADMIN'],
            ['id' => 3, 'name' => 'Advertiser',           'code' => 'ADVERT'],
        ]);
    }

    public function test_unauthenticated_request_to_protected_route_returns_401(): void
    {
        $response = $this->getJson('/api/advertiser_back/1');

        $response->assertStatus(401);
    }

    public function test_advertiser_cannot_access_admin_routes(): void
    {
        $advertiser = User::factory()->create([
            'profil_id' => ProfilCode::Advertiser->value,
            'status'    => true,
        ]);

        $response = $this->actingAs($advertiser, 'sanctum')
                         ->getJson('/api/users_back');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_routes(): void
    {
        $admin = User::factory()->create([
            'profil_id' => ProfilCode::Admin->value,
            'status'    => true,
        ]);

        $response = $this->actingAs($admin, 'sanctum')
                         ->getJson('/api/dashboard_admin');

        $response->assertStatus(200);
    }
}
