<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class FruitDetectionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create and authenticate a user for testing
        Sanctum::actingAs(
            User::factory()->create(),
            ['*'] // Give user all abilities
        );
    }

    public function test_rejects_invalid_image()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->post('/api/detect-ripeness', [
            'image' => 'invalid-data',
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['image']);
    }

    public function test_accepts_valid_image()
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('fruit.jpg');

        $response = $this->withHeaders([
            'Accept' => 'application/json'
        ])->post('/api/detect-ripeness', [
            'image' => $file,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'fruit_type',
                     'ripeness',
                     'confidence',
                     'timestamp',
                     'details'
                 ]);
    }
}