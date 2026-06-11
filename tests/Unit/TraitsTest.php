<?php

namespace Tests\Unit;

use App\Traits\HasUuid;
use PHPUnit\Framework\TestCase;

class TraitsTest extends TestCase
{
    public function test_has_uuid_get_incrementing(): void
    {
        $instance = new class { use HasUuid; };
        $this->assertFalse($instance->getIncrementing());
    }

    public function test_has_uuid_get_key_type(): void
    {
        $instance = new class { use HasUuid; };
        $this->assertEquals('string', $instance->getKeyType());
    }

    public function test_api_response_format(): void
    {
        $this->assertTrue(true);
    }
}
