<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

class HelpersTest extends TestCase
{
    public function test_cache_key(): void
    {
        $this->assertEquals('dwakfjib:user:1', cache_key('user', 1));
        $this->assertEquals('dwakfjib:settings:app', cache_key('settings', 'app'));
    }

    public function test_format_currency(): void
    {
        $this->assertEquals('100.00 MAD', format_currency(100));
        $this->assertEquals('150.50 MAD', format_currency(150.5));
        $this->assertEquals('0.00 MAD', format_currency(0));
    }

    public function test_format_currency_custom(): void
    {
        $this->assertEquals('100.00 EUR', format_currency(100, 'EUR'));
    }

    public function test_generate_order_number(): void
    {
        $number = generate_order_number();
        $this->assertStringStartsWith('ORD-', $number);
        $this->assertEquals(19, strlen($number));
    }

    public function test_calculate_distance(): void
    {
        $lat1 = 31.6295; $lon1 = -7.9811;
        $lat2 = 33.5731; $lon2 = -7.5898;
        $distance = calculate_distance($lat1, $lon1, $lat2, $lon2);
        $this->assertGreaterThan(200, $distance);
        $this->assertLessThan(250, $distance);
    }

    public function test_calculate_distance_same_point(): void
    {
        $this->assertEquals(0.0, calculate_distance(0, 0, 0, 0));
    }

    public function test_calculate_distance_far(): void
    {
        $distance = calculate_distance(0, 0, 90, 0);
        $this->assertGreaterThan(10000, $distance);
    }
}
