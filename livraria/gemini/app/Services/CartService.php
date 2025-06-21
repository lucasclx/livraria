<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Livro;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function getOrCreateCart()
    {
        if (Auth::check()) {
            return $this->getAuthenticatedUserCart();
        }
        
        return $this->getGuestCart();
    }

    private function getAuthenticatedUserCart()
    {
        // Buscar carrinho do usuário
        $cart = Cart::where('user_id', Auth::id())
                   ->where('status', 'active')
                   ->first();

        if (!$cart) {
            // Verificar se existe carrinho de sessão para migrar
            $guestCart = $this->getGuestCart();
            if ($guestCart && $guestCart->items()->count() > 0) {
                return $this->migrateGuestCart($guestCart);
            }

            // Criar novo carrinho
            $cart = Cart::create([
                'user_id' => Auth::id(),
                'status' => 'active'
            ]);
        }

        return $cart;
    }

    private function getGuestCart()
    {
        $sessionId = session()->getId();
        
        $cart = Cart::where('session_id', $sessionId)
                   ->where('status', 'active')
                   ->whereNull('user_id')
                   ->first();

        if (!$cart) {
            $cart = Cart::create([
                'session_id' => $sessionId,
                'status' => 'active'
            ]);
        }

        return $cart;
    }

    private function migrateGuestCart($guestCart)
    {
        return DB::transaction(function () use ($guestCart) {
            // Criar carrinho para usuário logado
            $userCart = Cart::create([
                'user_id' => Auth::id(),
                'status' => 'active'
            ]);

            // Migrar itens
            foreach ($guestCart->items as $item) {
                // Verificar se item já existe no carrinho do usuário
                $existingItem = $userCart->items()
                    ->where('livro_id', $item->livro_id)
                    ->first();

                if ($existingItem) {
                    $existingItem->increment('quantity', $item->quantity);
                } else {
                    CartItem::create([
                        'cart_id' => $userCart->id,
                        'livro_id' => $item->livro_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price
                    ]);
                }
            }

            // Remover carrinho de sessão
            $guestCart->delete();

            return $userCart;
        });
    }

    public function addItem($livroId, $quantity = 1)
    {
        return DB::transaction(function () use ($livroId, $quantity) {
            $livro = Livro::findOrFail($livroId);
            
            // Validações
            if (!$livro->ativo || $livro->estoque < $quantity) {
                throw new \Exception('Produto indisponível ou quantidade insuficiente');
            }

            $cart = $this->getOrCreateCart();
            $existingItem = $cart->items()->where('livro_id', $livroId)->first();

            if ($existingItem) {
                $newQuantity = $existingItem->quantity + $quantity;
                if ($newQuantity > $livro->estoque) {
                    throw new \Exception('Quantidade solicitada excede estoque disponível');
                }
                $existingItem->update(['quantity' => $newQuantity]);
                return $existingItem;
            }

            return CartItem::create([
                'cart_id' => $cart->id,
                'livro_id' => $livroId,
                'quantity' => $quantity,
                'price' => $livro->preco_final
            ]);
        });
    }

    public function updateItem($itemId, $quantity)
    {
        return DB::transaction(function () use ($itemId, $quantity) {
            $item = CartItem::findOrFail($itemId);
            
            if ($item->livro->estoque < $quantity) {
                throw new \Exception('Quantidade solicitada excede estoque disponível');
            }

            if ($quantity <= 0) {
                $item->delete();
                return null;
            }

            $item->update(['quantity' => $quantity]);
            return $item;
        });
    }

    public function removeItem($itemId)
    {
        CartItem::findOrFail($itemId)->delete();
    }

    public function clearCart($cartId = null)
    {
        $cart = $cartId ? Cart::find($cartId) : $this->getOrCreateCart();
        if ($cart) {
            $cart->items()->delete();
        }
    }

    public function getCartTotal($cartId = null)
    {
        $cart = $cartId ? Cart::find($cartId) : $this->getOrCreateCart();
        return $cart ? $cart->items()->sum(DB::raw('quantity * price')) : 0;
    }

    public function getCartCount($cartId = null)
    {
        $cart = $cartId ? Cart::find($cartId) : $this->getOrCreateCart();
        return $cart ? $cart->items()->sum('quantity') : 0;
    }

    public function validateCartStock($cartId = null)
    {
        $cart = $cartId ? Cart::find($cartId) : $this->getOrCreateCart();
        $errors = [];

        foreach ($cart->items as $item) {
            if (!$item->livro->ativo) {
                $errors[] = "O livro '{$item->livro->titulo}' não está mais disponível";
            } elseif ($item->livro->estoque < $item->quantity) {
                $errors[] = "Estoque insuficiente para '{$item->livro->titulo}'. Disponível: {$item->livro->estoque}";
            }
        }

        return $errors;
    }

    public function reserveStock($cartId)
    {
        return DB::transaction(function () use ($cartId) {
            $cart = Cart::findOrFail($cartId);
            
            foreach ($cart->items as $item) {
                $livro = $item->livro;
                if ($livro->estoque < $item->quantity) {
                    throw new \Exception("Estoque insuficiente para {$livro->titulo}");
                }
                
                // Reservar estoque (diminuir temporariamente)
                $livro->decrement('estoque', $item->quantity);
                
                // Registrar reserva para poder desfazer se necessário
                $item->update(['stock_reserved' => true]);
            }
        });
    }

    public function releaseStock($cartId)
    {
        DB::transaction(function () use ($cartId) {
            $cart = Cart::findOrFail($cartId);
            
            foreach ($cart->items as $item) {
                if ($item->stock_reserved) {
                    $item->livro->increment('estoque', $item->quantity);
                    $item->update(['stock_reserved' => false]);
                }
            }
        });
    }
}