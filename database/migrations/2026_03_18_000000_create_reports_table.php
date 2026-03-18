<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = (string) config('corepine-reportable.table_prefix', '') . 'reports';

        Schema::create($tableName, function (Blueprint $table): void {
            $table->id();
            $table->nullableMorphs('reporter');
            $table->morphs('reportable');
            $table->string('type');
            $table->string('status')->default('pending');
            $table->json('data')->nullable();
            $table->timestamps();

            $table->index(['reportable_type', 'reportable_id', 'status'], 'corepine_reports_reportable_status_index');
            $table->index(['reportable_type', 'reportable_id', 'type'], 'corepine_reports_reportable_type_index');
            $table->index(['reporter_type', 'reporter_id'], 'corepine_reports_reporter_index');
            $table->unique(
                ['reporter_type', 'reporter_id', 'reportable_type', 'reportable_id'],
                'corepine_reports_reporter_target_unique'
            );
        });
    }

    public function down(): void
    {
        $tableName = (string) config('corepine-reportable.table_prefix', '') . 'reports';

        Schema::dropIfExists($tableName);
    }
};
