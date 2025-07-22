<?php
namespace App\Policies;

use App\Models\Cart;
use App\Models\User;

class CartPolicy
{
    public function view(User $authUser, Cart $cart): bool
    {
        return $authUser->id === $cart->user_id;
    }

    public function update(User $authUser, Cart $cart): bool
    {
        return $authUser->id === $cart->user_id;
    }

    public function delete(User $authUser, Cart $cart): bool
    {
        return $authUser->id === $cart->user_id;
    }
}