<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Medication;
use App\Models\Pharmacy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MedicationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_medications(): void
    {
        Medication::factory()->count(3)->create();

        $response = $this->getJson('/api/medications');

        $response->assertStatus(200);
    }

    public function test_can_search_medications(): void
    {
        Medication::factory()->create(['name' => 'Paracetamol 500mg']);
        Medication::factory()->create(['name' => 'Ibuprofen 400mg']);

        $response = $this->getJson('/api/medications?search=Paracetamol');

        $response->assertStatus(200);
    }

    public function test_can_filter_by_category(): void
    {
        Medication::factory()->create(['category' => 'Pain Relief']);
        Medication::factory()->create(['category' => 'Antibiotic']);

        $response = $this->getJson('/api/medications?category=Pain Relief');

        $response->assertStatus(200);
    }

    public function test_can_show_single_medication(): void
    {
        $medication = Medication::factory()->create();

        $response = $this->getJson("/api/medications/{$medication->id}");

        $response->assertStatus(200);
    }

    public function test_returns_404_for_missing_medication(): void
    {
        $response = $this->getJson('/api/medications/99999');
        $response->assertStatus(404);
    }

    public function test_pharmacy_user_can_create_medication(): void
    {
        $user = User::factory()->pharmacy()->create();
        $pharmacy = Pharmacy::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->postJson('/api/medications', [
            'name' => 'Amoxicillin 500mg',
            'dosage' => '500mg',
            'price' => 45.00,
            'stock' => 100,
            'category' => 'Antibiotic',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('medications', ['name' => 'Amoxicillin 500mg']);
    }

    public function test_non_pharmacy_user_cannot_create_medication(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_USER]);

        $response = $this->actingAs($user)->postJson('/api/medications', [
            'name' => 'Amoxicillin 500mg',
            'dosage' => '500mg',
            'price' => 45.00,
            'stock' => 100,
            'category' => 'Antibiotic',
        ]);

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_cannot_create_medication(): void
    {
        $response = $this->postJson('/api/medications', [
            'name' => 'Test Medication',
        ]);

        $response->assertStatus(401);
    }

    public function test_can_update_medication(): void
    {
        $medication = Medication::factory()->create();
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->putJson("/api/medications/{$medication->id}", [
                'name' => 'Updated Name',
                'price' => 99.99,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('medications', ['name' => 'Updated Name']);
    }

    public function test_can_adjust_stock(): void
    {
        $medication = Medication::factory()->create(['stock' => 50]);
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->postJson("/api/medications/{$medication->id}/adjust-stock", [
                'new_stock' => 200,
                'reason' => 'Restock',
            ]);

        $response->assertStatus(200);
        $this->assertEquals(200, $medication->fresh()->stock);
    }

    public function test_can_purchase_medication(): void
    {
        $medication = Medication::factory()->create(['stock' => 50]);
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->postJson("/api/medications/{$medication->id}/purchase", [
                'quantity' => 5,
            ]);

        $response->assertStatus(200);
        $this->assertEquals(45, $medication->fresh()->stock);
    }

    public function test_purchase_fails_on_insufficient_stock(): void
    {
        $medication = Medication::factory()->create(['stock' => 2]);
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->postJson("/api/medications/{$medication->id}/purchase", [
                'quantity' => 10,
            ]);

        $response->assertStatus(400);
    }

    public function test_scan_barcode(): void
    {
        $medication = Medication::factory()->create([
            'barcode' => 'DWF202606121234',
        ]);

        $response = $this->getJson("/api/medications/barcode/{$medication->barcode}");

        $response->assertStatus(200);
    }

    public function test_scan_barcode_returns_404(): void
    {
        $response = $this->getJson('/api/medications/barcode/NONEXISTENT');
        $response->assertStatus(404);
    }

    public function test_can_delete_medication(): void
    {
        $medication = Medication::factory()->create();
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->deleteJson("/api/medications/{$medication->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted($medication);
    }

    public function test_can_view_stock_history(): void
    {
        $medication = Medication::factory()->create(['stock' => 100]);
        $pharmacyOwner = User::factory()->pharmacy()->create();
        $medication->pharmacy->update(['user_id' => $pharmacyOwner->id]);

        $response = $this->actingAs($pharmacyOwner)
            ->getJson("/api/medications/{$medication->id}/stock-history");

        $response->assertStatus(200);
    }
}
