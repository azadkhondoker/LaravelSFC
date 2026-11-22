<?php

namespace Tests\Feature;

use App\Models\posts;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostsTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_date_is_saved_and_can_be_updated(): void
    {
        $this->actingAs(User::factory()->create());

        $this->post(route('posts.store'), [
            'title' => 'Release notes',
            'content' => 'A summary of the latest changes.',
            'post_date' => '2026-11-18',
        ])->assertRedirect(route('posts.index'));

        $post = posts::firstOrFail();
        $this->assertSame('2026-11-18', $post->post_date->format('Y-m-d'));
        $this->get(route('posts.edit', $post))
            ->assertOk()
            ->assertSee('value="2026-11-18"', false);

        $this->put(route('posts.update', $post), [
            'title' => 'Updated release notes',
            'content' => 'An updated summary.',
            'post_date' => '2026-11-20',
        ])->assertRedirect(route('posts.index'));

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated release notes',
        ]);
        $this->assertSame('2026-11-20', $post->fresh()->post_date->format('Y-m-d'));
        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Nov 20, 2026')
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee('Updated release notes')
            ->assertSee('An updated summary.');
    }
}
