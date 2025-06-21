<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserAddress;

class UserAddressPolicy
{
    /**
     * Determina se o usuário pode ver qualquer endereço
     */
    public function viewAny(User $user): bool
    {
        return true; // Usuário pode ver seus próprios endereços
    }

    /**
     * Determina se o usuário pode ver o endereço
     */
    public function view(User $user, UserAddress $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determina se o usuário pode criar endereços
     */
    public function create(User $user): bool
    {
        // Limitar a 5 endereços por usuário
        return $user->addresses()->count() < 5;
    }

    /**
     * Determina se o usuário pode atualizar o endereço
     */
    public function update(User $user, UserAddress $address): bool
    {
        return $user->id === $address->user_id;
    }

    /**
     * Determina se o usuário pode excluir o endereço
     */
    public function delete(User $user, UserAddress $address): bool
    {
        // Não pode excluir se for o único endereço
        if ($user->addresses()->count() <= 1) {
            return false;
        }

        return $user->id === $address->user_id;
    }
}