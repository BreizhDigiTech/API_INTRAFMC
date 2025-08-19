<?php

namespace App\Modules\Order\GraphQL\Mutations;

use App\Exceptions\CustomException;
use App\Modules\Order\Services\OrderService;
use Illuminate\Support\Facades\Gate;
use App\Models\Order;
use App\Helpers\AuthHelper;

class OrderMutator
{
    protected $service;

    public function __construct()
    {
        $this->service = new OrderService();
    }

    /**
     * Valide une commande pour l'utilisateur connecte.
     *
     * @return Order
     * @throws CustomException
     */
    public function checkout()
    {
        $user = AuthHelper::ensureAuthenticated();

        if (!Gate::allows('create', Order::class)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour passer une commande.');
        }

        try {
            $order = $this->service->checkout($user->id);
            // Retourne directement la commande telle qu'attendue par le schema GraphQL
            return $order;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            throw new CustomException('Panier introuvable', 'Aucun panier trouve pour cet utilisateur.');
        } catch (\Exception $e) {
            if ($e->getMessage() === 'Le panier est vide.') {
                throw new CustomException('Panier vide', 'Votre panier est vide, impossible de passer commande.');
            }
            throw new CustomException('Erreur interne', 'Impossible de valider la commande.');
        }
    }

    /**
     * Annule une commande existante.
     *
     * @param mixed $_
     * @param array $args
     * @return bool
     * @throws CustomException
     */
    public function cancelOrder($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();

        $order = Order::findOrFail($args['id']);
        if (!Gate::allows('delete', $order)) {
            throw new CustomException('Acces refuse', 'Vous n\'avez pas les permissions necessaires pour annuler cette commande.');
        }

        try {
            $this->service->cancelOrder($args['id'], $user->id);
            return true;
        } catch (CustomException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible d\'annuler la commande.');
        }
    }

    /**
     * Met à jour le statut d'une commande (Admin uniquement).
     *
     * @param mixed $_
     * @param array $args
     * @return Order
     * @throws CustomException
     */
    public function updateOrderStatus($_, array $args)
    {
        $user = AuthHelper::ensureAuthenticated();
        
        // Seuls les admins peuvent changer le statut
        if (!$user->is_admin) {
            throw new CustomException('Accès refusé', 'Seuls les administrateurs peuvent modifier le statut des commandes.');
        }

        $input = $args['input'];

        try {
            $order = $this->service->updateOrderStatus($input['id'], $input['status']);
            return $order->load(['user', 'products']);
        } catch (CustomException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CustomException('Erreur interne', 'Impossible de mettre à jour le statut de la commande.');
        }
    }
}