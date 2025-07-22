<?php

namespace App\Modules\Arrival\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Arrival\Services\ArrivalService;
use Illuminate\Support\Facades\Gate;
use App\Models\CbdArrival;
use App\Helpers\AuthHelper;

class ArrivalMutator
{
    /**
     * Verifie si un arrivage existe ou leve une exception.
     *
     * @param int $arrivalId
     * @return CbdArrival
     * @throws CustomException
     */
    private function findArrivalOrFail($arrivalId)
    {
        $arrival = CbdArrival::find($arrivalId);
        if (!$arrival) {
            throw new CustomException('Arrivage introuvable', 'L\'arrivage specifie est introuvable.');
        }
        return $arrival;
    }

    /**
     * Valide les donnees d'entree pour un arrivage.
     *
     * @param array $input
     * @return array
     * @throws CustomException
     */
    private function validateArrivalInput(array $input)
    {
        return validator($input, [
            'amount' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,validated',
            'products' => 'required|array',
            'products.*.product_id' => 'required|integer|exists:cbd_products,id',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
        ])->validate();
    }

    /**
     * Valide un arrivage.
     *
     * @param mixed $root
     * @param array $args
     * @return CbdArrival
     * @throws CustomException
     */
    public function validateArrival($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $arrival = $this->findArrivalOrFail($args['arrival_id']);

        if (!Gate::allows('update', $arrival)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour valider cet arrivage.');
        }

        try {
            $validatedArrival = app(ArrivalService::class)->validateArrival($args['arrival_id']);
            // Retourne directement l'arrivage tel qu'attendu par le schema GraphQL
            return $validatedArrival;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de valider l\'arrivage.');
        }
    }

    /**
     * Cree un nouvel arrivage.
     *
     * @param mixed $root
     * @param array $args
     * @return CbdArrival
     * @throws CustomException
     */
    public function createArrival($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $validatedData = $this->validateArrivalInput($args['input']);

        if (!Gate::allows('create', CbdArrival::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour creer un arrivage.');
        }

        try {
            $createdArrival = app(ArrivalService::class)->createArrival($validatedData);
            // Retourne directement l'arrivage tel qu'attendu par le schema GraphQL
            return $createdArrival;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de creer l\'arrivage.');
        }
    }

    /**
     * Met a jour un arrivage existant.
     *
     * @param mixed $root
     * @param array $args
     * @return CbdArrival
     * @throws CustomException
     */
    public function updateArrival($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $arrival = $this->findArrivalOrFail($args['arrival_id']);

        if (!Gate::allows('update', $arrival)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour modifier cet arrivage.');
        }

        try {
            $updatedArrival = app(ArrivalService::class)->updateArrival($args['arrival_id'], $args['input']);
            // Retourne directement l'arrivage tel qu'attendu par le schema GraphQL
            return $updatedArrival;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de modifier l\'arrivage.');
        }
    }

    /**
     * Supprime un arrivage.
     *
     * @param mixed $root
     * @param array $args
     * @return CbdArrival
     * @throws CustomException
     */
    public function deleteArrival($root, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $arrival = $this->findArrivalOrFail($args['arrival_id']);

        if (!Gate::allows('delete', $arrival)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour supprimer cet arrivage.');
        }

        try {
            $deletedArrival = app(ArrivalService::class)->deleteArrival($args['arrival_id']);
            // Retourne directement l'arrivage tel qu'attendu par le schema GraphQL
            return $deletedArrival;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de supprimer l\'arrivage.');
        }
    }
}