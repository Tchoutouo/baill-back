<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Insert profils directly — avoids Profil::truncate() which causes an
        // implicit MySQL commit that breaks the RefreshDatabase transaction wrapper.
        DB::table('profils')->insertOrIgnore([
            ['id' => 1, 'name' => 'Super Administrateur', 'code' => 'SUP_ADMIN'],
            ['id' => 2, 'name' => 'Administrateur',       'code' => 'ADMIN'],
            ['id' => 3, 'name' => 'Advertiser',           'code' => 'ADVERT'],
        ]);
    }

    public function test_new_advertiser_can_register(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/advertiser_back/store', [
            'username'         => 'testuser',
            'first_name'       => 'Test',
            'last_name'        => 'User',
            'email'            => 'test@example.com',
            'whatsapp_number'  => '+237612345678',
            'country'          => 'Cameroun',
            'city'             => 'Douala',
            'neighborhood'     => 'Akwa',
        ]);

        $response->assertStatus(201)
                 ->assertJsonPath('success', true);

        $this->assertDatabaseHas('users', [
            'email'    => 'test@example.com',
            'username' => 'testuser',
        ]);
    }
}
