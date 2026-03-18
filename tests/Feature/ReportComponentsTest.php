<?php

declare(strict_types=1);

use Corepine\Reportable\Enums\ReportType;
use Illuminate\Support\Facades\Blade;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

it('renders the report button and modal components', function (): void {
    $post = Post::query()->create([
        'title' => 'Rendered post',
    ]);

    $html = Blade::render('<x-corepine-report-button :reportable="$post" label="Flag this" />', [
        'post' => $post,
    ]);

    expect($html)->toContain('Flag this')
        ->and($html)->toContain('name="type"')
        ->and($html)->toContain('Obscene Content');
});

it('stores a report through the package route', function (): void {
    $user = User::query()->create([
        'name' => 'Reporter',
        'email' => 'route@example.com',
        'password' => 'secret',
    ]);

    $post = Post::query()->create([
        'title' => 'Route post',
    ]);

    $this->actingAs($user)
        ->post(route('corepine-reportable.store'), [
            'reportable_type' => $post->getMorphClass(),
            'reportable_id' => (string) $post->getKey(),
            'type' => ReportType::SPAM->value,
            'data' => [
                'message' => 'Looks like spam.',
            ],
        ])
        ->assertSessionHas('reportable.status');

    expect($post->fresh()->reportsCount())->toBe(1);
});

it('renders the report inbox page', function (): void {
    $user = User::query()->create([
        'name' => 'Moderator',
        'email' => 'moderator@example.com',
        'password' => 'secret',
    ]);

    $post = Post::query()->create([
        'title' => 'Inbox post',
    ]);

    $user->report($post, 'spam', [
        'message' => 'Inbox message',
    ]);

    $this->actingAs($user)
        ->get(route('corepine-reportable.index'))
        ->assertOk()
        ->assertSee('Report Inbox')
        ->assertSee('Spam')
        ->assertSee('Inbox message');
});
