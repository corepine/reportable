<?php

declare(strict_types=1);

namespace Workbench\App\Models;

use Corepine\Reportable\Models\Concerns\CanReport;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use CanReport;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];
}
