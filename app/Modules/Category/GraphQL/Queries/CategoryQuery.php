<?php

namespace App\Modules\Category\GraphQL\Queries;

use App\Helpers\AuthHelper;
use App\Models\Category;

class CategoryQuery
{
	public function categories($root, array $args)
	{
		AuthHelper::ensureAuthenticated();

		return Category::query()->orderByDesc('created_at');
	}
}

