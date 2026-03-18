<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Corepine\Reportable\Models\Concerns\HasReports;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasReports;

    protected $fillable = [
        'title',
    ];
}
