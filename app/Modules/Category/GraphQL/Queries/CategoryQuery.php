<?php

namespace App\Modules\Category\GraphQL\Queries;

use App\Models\Category;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;

class CategoryQuery
{
    /**
     * Récupère la liste des catégories.
     *
     * @return array
     * @throws CustomException
     */
    public function categories()
    {
        // Vérifie les permissions
        if (!Gate::allows('viewAny', Category::class)) {
            throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir la liste des catégories.');
        }

        try {
            $categories = Category::with('products')->get();
            // Retourne directement la liste des catégories tel qu'attendu par le schéma GraphQL
            return $categories;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des catégories.');
        }
    }

    /**
     * Récupère une catégorie spécifique.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function category($_, array $args)
    {
        try {
            // Récupère la catégorie
            $category = Category::with('products')->find($args['id']);
            if (!$category) {
                throw new CustomException('Catégorie introuvable', "Aucune catégorie n'a été trouvée avec cet identifiant.");
            }

            // Vérifie les permissions
            if (!Gate::allows('view', $category)) {
                throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir cette catégorie.');
            }

            // Retourne directement la catégorie tel qu'attendu par le schéma GraphQL
            return $category;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la catégorie.');
        }
    }
}