<?php

namespace Tests\Feature;

use Tests\TestCase;

class PharmacyApiTest extends TestCase
{
    public function test_api_returns_404_for_unknown_route(): void
    {
        $response = $this->get('/api/nonexistent');
        $response->assertStatus(404);
    }
}
