<?php

namespace Tests\Feature;

use App\Models\Type;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The registration screen's exam-type list. The mobile app casts `decription`
 * and `price` straight to String, so a null in either fails the whole list.
 */
class ExamTypeApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_nullable_columns_are_sent_as_strings(): void
    {
        Type::create(['name' => 'No Price Or Description']);
        Type::create(['name' => 'Freshman', 'description' => 'All subjects', 'price' => '500']);

        $response = $this->getJson('/api/exam-type')->assertOk();

        $this->assertSame([
            ['name' => 'No Price Or Description', 'decription' => '', 'price' => ''],
            ['name' => 'Freshman', 'decription' => 'All subjects', 'price' => '500'],
        ], collect($response->json('data'))->map->only(['name', 'decription', 'price'])->all());
    }
}
