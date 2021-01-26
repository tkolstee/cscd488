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
        $this->assertTrue(is_subclass_of($payloads[0], 'App\Models\Payload'));
    }

    public function testGetPayloadByTag() {
        $tag = 'EndpointExecutable';
        $payloads = Payload::getByTag($tag);
        $this->assertNotEquals(0, count($payloads));
        foreach ($payloads as $payload){
            $this->assertTrue(in_array($tag, $payload->tags));
        }
    }
}
