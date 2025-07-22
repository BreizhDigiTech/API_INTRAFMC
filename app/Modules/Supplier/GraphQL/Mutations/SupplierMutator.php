<?php

namespace App\Modules\Supplier\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Supplier\Services\SupplierService;
use Illuminate\Support\Facades\Gate;
use App\Models\Supplier;
use App\Helpers\AuthHelper;

class SupplierMutator
{
    protected $service;

    public function __construct()
    {
        $this->service = new SupplierService();
    }

    /**
     * Cree un nouveau fournisseur.
     *
     * @param mixed $_
     * @param array $args
     * @return Supplier
     * @throws CustomException
     */
    public function createSupplier($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', Supplier::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour creer un fournisseur.');
        }

        try {
            $supplier = $this->service->createSupplier($args);
            // Retourne directement le fournisseur tel qu'attendu par le schema GraphQL
            return $supplier;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de creer le fournisseur.');
        }
    }

    /**
     * Attache un fournisseur a un produit.
     *
     * @param mixed $_
     * @param array $args
     * @return Supplier
     * @throws CustomException
     */
    public function attachSupplierToProduct($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $supplier = Supplier::findOrFail($args['supplier_id']);

        if (!Gate::allows('update', $supplier)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour attacher un fournisseur a un produit.');
        }

        try {
            $updatedSupplier = $this->service->attachSupplierToProduct($args['supplier_id'], $args['product_id']);
            // Retourne directement le fournisseur tel qu'attendu par le schema GraphQL
            return $updatedSupplier;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible d\'attacher le fournisseur au produit.');
        }
    }

    /**
     * Detache un fournisseur d'un produit.
     *
     * @param mixed $_
     * @param array $args
     * @return Supplier
     * @throws CustomException
     */
    public function detachSupplierFromProduct($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $supplier = Supplier::findOrFail($args['supplier_id']);

        if (!Gate::allows('update', $supplier)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour detacher un fournisseur d\'un produit.');
        }

        try {
            $updatedSupplier = $this->service->detachSupplierFromProduct($args['supplier_id'], $args['product_id']);
            // Retourne directement le fournisseur tel qu'attendu par le schema GraphQL
            return $updatedSupplier;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de detacher le fournisseur du produit.');
        }
    }
}