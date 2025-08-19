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
            // Normalisation/validation minimale
            $payload = [
                'name' => isset($args['name']) ? trim((string) $args['name']) : null,
                'email' => isset($args['email']) ? trim((string) $args['email']) : null,
                'phone' => isset($args['phone']) ? trim((string) $args['phone']) : null,
                'address' => isset($args['address']) ? trim((string) $args['address']) : null,
                'website' => isset($args['website']) ? trim((string) $args['website']) : null,
                'contact_person' => isset($args['contact_person']) ? trim((string) $args['contact_person']) : null,
                'description' => isset($args['description']) ? trim((string) $args['description']) : null,
            ];

            if (!$payload['name']) {
                throw new CustomException('Validation', 'Le nom du fournisseur est obligatoire.');
            }
            if ($payload['email'] && !filter_var($payload['email'], FILTER_VALIDATE_EMAIL)) {
                throw new CustomException('Validation', 'Le champ email n\'est pas valide.');
            }

            $supplier = $this->service->createSupplier($payload);
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

    /**
     * Met à jour un fournisseur.
     *
     * @param mixed $_
     * @param array $args
     * @return Supplier
     * @throws CustomException
     */
    public function updateSupplier($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $supplier = Supplier::findOrFail($args['id']);

        if (!Gate::allows('update', $supplier)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier ce fournisseur.');
        }

        try {
            $input = $args['input'];
            
            // Validation de l'email si fourni
            if (isset($input['email']) && $input['email'] && !filter_var($input['email'], FILTER_VALIDATE_EMAIL)) {
                throw new CustomException('Validation', 'Le champ email n\'est pas valide.');
            }

            $updatedSupplier = $this->service->updateSupplier($args['id'], $input);
            return $updatedSupplier;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de modifier le fournisseur.');
        }
    }

    /**
     * Supprime un fournisseur.
     *
     * @param mixed $_
     * @param array $args
     * @return array
     * @throws CustomException
     */
    public function deleteSupplier($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        $supplier = Supplier::findOrFail($args['id']);

        if (!Gate::allows('delete', $supplier)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour supprimer ce fournisseur.');
        }

        try {
            $success = $this->service->deleteSupplier($args['id']);
            return [
                'success' => $success,
                'message' => $success ? 'Fournisseur supprime avec succes' : 'Echec de la suppression'
            ];
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer le fournisseur.');
        }
    }
}