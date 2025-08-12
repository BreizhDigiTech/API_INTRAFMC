<?php

namespace App\Modules\Supplier\GraphQL\Queries;

use App\Models\Supplier;
use App\Helpers\AuthHelper;
use Illuminate\Support\Facades\Gate;

class SupplierQuery
{
    public function suppliers($root, array $args)
    {
        AuthHelper::ensureAuthenticated();

        if (Gate::allows('viewAny', Supplier::class)) {
            return Supplier::query();
        }

        return Supplier::query()->whereRaw('1=0');
    }
}
