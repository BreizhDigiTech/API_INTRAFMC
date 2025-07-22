<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class GraphQLSchemaServiceProvider extends ServiceProvider
{
    public function register()
    {
        $schemas = [];

        // Recherche dans tous les modules
        $modulePaths = File::directories(base_path('app/Modules'));

        foreach ($modulePaths as $modulePath) {
            $schemaPath = $modulePath . '/GraphQL/schema.graphql';

            if (File::exists($schemaPath)) {
                $schemas[] = $schemaPath;
            }
        }
        // Injecte les chemins dans la config Lighthouse
        Config::set('lighthouse.schema', $schemas);
    }
    
}
