<?php

namespace App\Modules\Supplier\GraphQL\Queries;

use App\Models\Supplier;
use Illuminate\Support\Facades\Gate;
use App\Exceptions\CustomException;
use App\Helpers\AuthHelper;

class SupplierQuery
{
    /**
     * Recupere la liste des fournisseurs.
     *
     * @return array
     * @throws CustomException
     */
    public function suppliers()
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('viewAny', Supplier::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir la liste des fournisseurs.');
        }

        try {
            $suppliers = Supplier::with('products')->get();
            // Retourne directement le tableau de fournisseurs tel qu'attendu par le schema GraphQL
            return $suppliers;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de recuperer la liste des fournisseurs.');
        }
    }

    /**
     * Recupere un fournisseur specifique.
     *
     * @param mixed $_
     * @param array $args
     * @return Supplier
     * @throws CustomException
     */
    public function supplier($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $supplier = Supplier::with('products')->find($args['id']);

        if (!$supplier) {
            throw new CustomException('Fournisseur introuvable', "Aucun fournisseur n'a ete trouve avec cet identifiant.");
        }

        if (!Gate::allows('view', $supplier)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour voir ce fournisseur.');
        }

        // Retourne directement le fournisseur tel qu'attendu par le schema GraphQL
        return $supplier;
    }
}