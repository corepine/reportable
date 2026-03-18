<?php

declare(strict_types=1);

namespace Corepine\Reportable\Console\Commands;

use Corepine\Reportable\ReportableServiceProvider;
use Illuminate\Console\Command;

class InstallReportableCommand extends Command
{
    protected $signature = 'reportable:install
        {--force : Overwrite any existing published files}
        {--migrate : Run database migrations after publishing}';

    protected $description = 'Install corepine/reportable by publishing config, migrations, and views';

    public function handle(): int
    {
        $this->comment('Installing corepine/reportable...');

        $this->comment('Publishing configuration...');
        $this->publishTag('corepine-reportable-config');

        $this->comment('Publishing migrations...');
        $this->publishTag('corepine-reportable-migrations');

        $this->comment('Publishing views...');
        $this->publishTag('corepine-reportable-views');

        if ($this->option('migrate')) {
            $this->comment('Running migrations...');
            $this->call('migrate');
        }

        $this->info('[✓] corepine/reportable installed successfully.');

        return self::SUCCESS;
    }

    private function publishTag(string $tag): void
    {
        $params = [
            '--provider' => ReportableServiceProvider::class,
            '--tag' => $tag,
        ];

        if ($this->option('force')) {
            $params['--force'] = true;
        }

        $this->call('vendor:publish', $params);
    }
}
