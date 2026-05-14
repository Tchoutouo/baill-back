<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginTest extends TestCase
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

    public function test_advertiser_can_login_with_email(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret1234'),
            'status'   => true,
        ]);

        $response = $this->postJson('/api/login', [
            'identifiant' => $user->email,
            'password'    => 'secret1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure(['token', 'data', 'redirect_url']);
    }

    public function test_advertiser_can_login_with_whatsapp_number(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret1234'),
            'status'   => true,
        ]);

        $response = $this->postJson('/api/login', [
            'identifiant' => $user->whatsapp_number,
            'password'    => 'secret1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('correct-password'),
            'status'   => true,
        ]);

        $response = $this->postJson('/api/login', [
            'identifiant' => $user->email,
            'password'    => 'wrong-password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', false);
    }

    public function test_blocked_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('secret1234'),
            'status'   => false,
        ]);

        $response = $this->postJson('/api/login', [
            'identifiant' => $user->email,
            'password'    => 'secret1234',
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', false);
    }
}
