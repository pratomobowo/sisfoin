<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Traits\HasCrudOperations;
use Spatie\Permission\Models\Role;

class RoleController extends BaseController
{
    use HasCrudOperations;

    protected string $modelClass = Role::class;
    protected string $viewPrefix = 'superadmin.roles';
    protected string $routePrefix = 'superadmin.roles';
    protected string $resourceName = 'Peran';
}
