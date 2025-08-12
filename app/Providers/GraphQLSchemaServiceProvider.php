<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

class GraphQLSchemaServiceProvider extends ServiceProvider
{
    public function register()
    {
        // Utiliser le fichier agrégateur unique qui #importe tous les schémas des modules
        $aggregateSchema = base_path('graphql/schema.graphql');

        // Par sécurité, si le fichier n’existe pas, on retombe sur le chargement automatique des modules
        if (File::exists($aggregateSchema)) {
            Config::set('lighthouse.schema', $aggregateSchema);
            return;
        }

        // Fallback: recherche dans tous les modules si l’agrégateur est absent
        $schemas = [];
        $modulePaths = File::directories(base_path('app/Modules'));

        foreach ($modulePaths as $modulePath) {
            $schemaPath = $modulePath . '/GraphQL/schema.graphql';
            if (File::exists($schemaPath)) {
                $schemas[] = $schemaPath;
            }
        }
        Config::set('lighthouse.schema', $schemas);
    }
    
}
