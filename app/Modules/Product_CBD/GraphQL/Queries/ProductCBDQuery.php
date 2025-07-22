<?php

namespace App\Modules\Product_CBD\GraphQL\Queries;

use App\Models\ProductCBD;
use App\Modules\Product_CBD\Services\ProductCBDService;
use App\Services\GraphQLCacheService;
use App\Exceptions\CustomException;
use Illuminate\Support\Facades\Gate;
use App\Helpers\AuthHelper;

class ProductCBDQuery
{
    protected $cacheService;

    public function __construct(GraphQLCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Récupère la liste des produits CBD.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function products($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        try {
            // Utilise le cache pour les produits avec filtres
            return $this->cacheService->getCachedProducts($args, function() use ($args) {
                return app(ProductCBDService::class)->getProducts($args);
            });
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer la liste des produits.');
        }
    }

    /**
     * Récupère un produit CBD spécifique.
     *
     * @param mixed $root
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function product($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        try {
            $product = app(ProductCBDService::class)->getProductById($args['id']);

            if (!$product) {
                throw new CustomException('Produit introuvable', "Aucun produit n'a été trouvé avec cet identifiant.");
            }

            if (!Gate::allows('view', $product)) {
                throw new CustomException('Accès refusé', 'Vous n’avez pas les permissions nécessaires pour voir ce produit.');
            }

            // Retourne directement le produit tel qu'attendu par le schéma GraphQL
            return $product;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de récupérer le produit.');
        }
    }
}