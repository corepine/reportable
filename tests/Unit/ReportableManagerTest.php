<?php

declare(strict_types=1);

use Corepine\Reportable\Services\ReportableManager;
use Workbench\App\Models\Post;

it('merges global and model specific report types', function (): void {
    config()->set('corepine-reportable.types', [
        'copyright' => [
            'label' => 'Copyright',
            'description' => 'Violates ownership rights.',
        ],
    ]);

    $post = new class extends Post
    {
        public function reportTypes(): array
        {
            return [
                'spoiler' => [
                    'label' => 'Spoiler',
                    'description' => 'Contains spoilers without warning.',
                ],
            ];
        }
    };

    $types = app(ReportableManager::class)->typeDefinitionsFor($post);

    expect($types)->toHaveKeys(['obscene', 'custom', 'copyright', 'spoiler'])
        ->and($types['spoiler']->label)->toBe('Spoiler')
        ->and($types['copyright']->description)->toBe('Violates ownership rights.');
});

it('validates supported statuses', function (): void {
    $manager = app(ReportableManager::class);

    expect($manager->ensureSupportedStatus('resolved'))->toBe('resolved')
        ->and(fn () => $manager->ensureSupportedStatus('unknown'))->toThrow(RuntimeException::class);
});
