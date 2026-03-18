<?php

declare(strict_types=1);

use Corepine\Reportable\Enums\ReportType;
use Workbench\App\Models\Post;
use Workbench\App\Models\User;

it('creates and updates reports through the concerns', function (): void {
    $reporter = User::query()->create([
        'name' => 'Namu',
        'email' => 'namu@example.com',
        'password' => 'secret',
    ]);

    $post = Post::query()->create([
        'title' => 'Hello world',
    ]);

    $report = $reporter->report($post, ReportType::OBSCENE, [
        'message' => 'Too explicit for this feed.',
    ]);

    expect($report->type)->toBe('obscene')
        ->and($post->reportsCount())->toBe(1)
        ->and($post->reportedBy($reporter))->toBeTrue();

    $updated = $reporter->report($post, ReportType::CUSTOM, [
        'message' => 'Different reason with more context.',
    ]);

    expect($post->reports()->count())->toBe(1)
        ->and($updated->type)->toBe('custom')
        ->and($updated->data['message'])->toBe('Different reason with more context.');
});

it('withdraws reports by reporter', function (): void {
    $reporter = User::query()->create([
        'name' => 'Namu',
        'email' => 'delete@example.com',
        'password' => 'secret',
    ]);

    $post = Post::query()->create([
        'title' => 'Withdraw me',
    ]);

    $reporter->report($post, 'spam');

    expect($post->reportsCount())->toBe(1);

    $deleted = $reporter->withdrawReport($post);

    expect($deleted)->toBe(1)
        ->and($post->reportsCount())->toBe(0);
});
