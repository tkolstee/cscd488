<?php

namespace Tests\Unit;

use App\Models\Payload;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PayloadTest extends TestCase {
    use RefreshDatabase;

    public function testGetAllPayloads() {
        $payloads = Payload::getAll();
        $this->assertNotEquals(0, count($payloads));
    }
}
