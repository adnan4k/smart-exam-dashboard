<?php

namespace Tests\Feature;

use App\Models\AppNotification;
use App\Models\NotificationComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_increments_like_and_dislike_counts()
    {
        $notification = AppNotification::create([
            'title' => 'Test title',
            'body' => 'Test body',
        ]);

        // like
        $this->postJson("/api/notifications/{$notification->id}/like")
            ->assertStatus(200)
            ->assertJsonPath('data.like_count', 1);

        // dislike
        $this->postJson("/api/notifications/{$notification->id}/dislike")
            ->assertStatus(200)
            ->assertJsonPath('data.dislike_count', 1);

        $fresh = $notification->fresh();
        $this->assertEquals(1, $fresh->like_count);
        $this->assertEquals(1, $fresh->dislike_count);
    }

    /** @test */
    public function it_creates_comment_and_increments_comment_count()
    {
        $user = User::factory()->create();

        $notification = AppNotification::create([
            'title' => 'Comment test',
            'body' => 'Body',
        ]);

        $payload = [
            'user_id' => $user->id,
            'comment' => 'This is helpful!',
        ];

        $this->postJson("/api/notifications/{$notification->id}/comment", $payload)
            ->assertStatus(200)
            ->assertJsonPath('data.comment_count', 1);

        $this->assertDatabaseHas('notification_comments', [
            'app_notification_id' => $notification->id,
            'user_id' => $user->id,
            'comment' => 'This is helpful!',
        ]);

        $fresh = $notification->fresh();
        $this->assertEquals(1, $fresh->comment_count);
    }
}


