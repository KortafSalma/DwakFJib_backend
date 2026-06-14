<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PharmacyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_pharmacies(): void
    {
        Pharmacy::factory()->count(3)->create();

        $response = $this->getJson('/api/pharmacies');

        $response->assertStatus(200);
    }

    public function test_can_show_pharmacy(): void
    {
        $pharmacy = Pharmacy::factory()->create();

        $response = $this->getJson("/api/pharmacies/{$pharmacy->id}");

        $response->assertStatus(200);
    }

    public function test_returns_404_for_missing_pharmacy(): void
    {
        $response = $this->getJson('/api/pharmacies/99999');
        $response->assertStatus(404);
    }

    public function test_can_get_pharmacy_medications(): void
    {
        $pharmacy = Pharmacy::factory()->create();
        \App\Models\Medication::factory()->count(3)->create([
            'pharmacy_id' => $pharmacy->id,
        ]);

        $response = $this->getJson("/api/pharmacies/{$pharmacy->id}/medications");

        $response->assertStatus(200);
    }

    public function test_api_returns_404_for_unknown_route(): void
    {
        $response = $this->getJson('/api/nonexistent');
        $response->assertStatus(404);
    }
}
