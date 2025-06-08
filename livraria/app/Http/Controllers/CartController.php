<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Livro;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartController extends Controller
{
    public function __construct(private PaymentService $paymentService)
    {
    }

    public function index()
    {
        $cart = $this->getCart();
        $items = $cart?->items()->with('livro')->get() ?? collect();
        $total = $items->sum(fn ($item) => $item->price * $item->quantity);
        $itemCount = $items->sum('quantity');

        // Sugestões baseadas no carrinho
        $sugestoes = $this->getSugestoes($items);

        return view('cart.index', compact('items', 'total', 'itemCount', 'sugestoes'));
    }

    public function add(Request $request, Livro $livro)
    {
        $request->validate([
            'quantity' => 'integer|min:1|max:10'
        ]);

        $quantity = max(1, (int) $request->input('quantity', 1));
        
        // Verificar disponibilidade
        if ($livro->estoque < $quantity) {
            return redirect()->back()->with('error', 'Quantidade solicitada não disponível em estoque.');
        }

        if (!$livro->ativo) {
            return redirect()->back()->with('error', 'Este livro não está mais disponível.');
        }

        $cart = $this->getOrCreateCart();

        try {
            DB::transaction(function() use ($cart, $livro, $quantity) {
                $item = $cart->items()->where('livro_id', $livro->id)->first();
                
                if ($item) {
                    // Verificar se a quantidade total não excede o estoque
                    $novaQuantidade = $item->quantity + $quantity;
                    if ($novaQuantidade > $livro->estoque) {
                        throw new \Exception('Quantidade total excede o estoque disponível.');
                    }
                    $item->increment('quantity', $quantity);
                } else {
                    $cart->items()->create([
                        'livro_id' => $livro->id,
                        'quantity' => $quantity,
                        'price' => $livro->preco,
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Livro adicionado ao carrinho!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function quickAdd(Request $request, Livro $livro)
    {
        // Para requisições AJAX
        if (!$livro->ativo || $livro->estoque < 1) {
            return response()->json(['success' => false, 'message' => 'Produto indisponível'], 400);
        }

        $cart = $this->getOrCreateCart();
        
        try {
            $item = $cart->items()->where('livro_id', $livro->id)->first();
            
            if ($item) {
                if ($item->quantity >= $livro->estoque) {
                    return response()->json(['success' => false, 'message' => 'Quantidade máxima atingida'], 400);
                }
                $item->increment('quantity');
            } else {
                $cart->items()->create([
                    'livro_id' => $livro->id,
                    'quantity' => 1,
                    'price' => $livro->preco,
                ]);
            }

            $cartCount = $cart->items()->sum('quantity');
            
            return response()->json([
                'success' => true, 
                'message' => 'Adicionado ao carrinho!',
                'cart_count' => $cartCount
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Erro interno'], 500);
        }
    }

    public function update(Request $request, CartItem $item)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:10'
        ]);

        $quantity = (int) $request->input('quantity');
        
        // Verificar se o item pertence ao carrinho da sessão
        $cart = $this->getCart();
        if (!$cart || $item->cart_id !== $cart->id) {
            return redirect()->route('cart.index')->with('error', 'Item não encontrado no seu carrinho.');
        }

        // Verificar disponibilidade
        if ($item->livro->estoque < $quantity) {
            return redirect()->route('cart.index')->with('error', 'Quantidade solicitada não disponível em estoque.');
        }

        $item->update(['quantity' => $quantity]);

        return redirect()->route('cart.index')->with('success', 'Quantidade atualizada!');
    }

    public function remove(CartItem $item)
    {
        // Verificar se o item pertence ao carrinho da sessão
        $cart = $this->getCart();
        if (!$cart || $item->cart_id !== $cart->id) {
            return redirect()->route('cart.index')->with('error', 'Item não encontrado no seu carrinho.');
        }

        $item->delete();
        
        return redirect()->route('cart.index')->with('success', 'Item removido do carrinho!');
    }

    public function clear()
    {
        $cart = $this->getCart();
        if ($cart) {
            $cart->items()->delete();
        }
        
        return redirect()->route('cart.index')->with('success', 'Carrinho limpo!');
    }

    public function checkout()
    {
        $cart = $this->getCart();
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $items = $cart->items()->with('livro')->get();
        
        // Verificar disponibilidade de todos os itens
        foreach ($items as $item) {
            if (!$item->livro->ativo || $item->livro->estoque < $item->quantity) {
                return redirect()->route('cart.index')->with('error', 
                    "O livro '{$item->livro->titulo}' não está mais disponível na quantidade solicitada.");
            }
        }

        $total = $items->sum(fn ($item) => $item->price * $item->quantity);
        $shipping = $this->calculateShipping($items);
        $grandTotal = $total + $shipping;

        return view('cart.checkout', compact('items', 'total', 'shipping', 'grandTotal'));
    }

    public function processCheckout(Request $request)
    {
        $cart = $this->getCart();
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $data = $request->validate([
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:50',
            'zip' => 'required|string|max:10',
            'country' => 'required|string|max:50',
            'payment_method' => 'required|in:credit_card,debit_card,pix,boleto'
        ]);

        try {
            DB::transaction(function() use ($cart, $data) {
                $items = $cart->items()->with('livro')->get();
                
                // Verificar estoque novamente
                foreach ($items as $item) {
                    if ($item->livro->estoque < $item->quantity) {
                        throw new \Exception("Estoque insuficiente para {$item->livro->titulo}");
                    }
                }

                $total = $items->sum(fn ($item) => $item->price * $item->quantity);
                $shipping = $this->calculateShipping($items);

                // Criar pedido
                $order = Order::create(array_merge($data, [
                    'cart_id' => $cart->id,
                    'user_id' => auth()->id(),
                    'total' => $total + $shipping,
                    'shipping_cost' => $shipping,
                    'status' => 'pending',
                ]));

                // Atualizar estoque (simulação)
                foreach ($items as $item) {
                    $item->livro->decrement('estoque', $item->quantity);
                }

                // Marcar carrinho como processado
                $cart->update([
                    'status' => 'completed',
                    'user_id' => auth()->id(),
                ]);
            });

            session()->forget('cart_id');
            
            return redirect()->route('orders.index')->with('success', 'Pedido realizado com sucesso!');
            
        } catch (\Exception $e) {
            Log::error('Erro no checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }
    }

    private function getCart(): ?Cart
    {
        $id = session('cart_id');
        return $id ? Cart::with('items')->find($id) : null;
    }

    private function getOrCreateCart(): Cart
    {
        if ($cart = $this->getCart()) {
            return $cart;
        }

        $cart = Cart::create([
            'session_id' => session()->getId(),
            'status' => 'active',
        ]);
        session(['cart_id' => $cart->id]);

        return $cart;
    }

    private function getSugestoes($items)
    {
        if ($items->isEmpty()) {
            return collect();
        }

        // Buscar livros da mesma categoria dos itens no carrinho
        $categorias = $items->pluck('livro.categoria')->filter()->unique();
        
        return Livro::whereIn('categoria', $categorias)
            ->whereNotIn('id', $items->pluck('livro.id'))
            ->where('ativo', true)
            ->where('estoque', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    private function calculateShipping($items)
    {
        // Simulação de cálculo de frete
        $totalWeight = $items->sum(function($item) {
            // Assumindo peso médio de 0.5kg por livro
            return $item->quantity * 0.5;
        });

        if ($totalWeight <= 1) {
            return 12.50; // PAC
        } elseif ($totalWeight <= 3) {
            return 18.90; // SEDEX
        } else {
            return 25.00; // Frete especial
        }
    }

    public function getCartCount()
    {
        $cart = $this->getCart();
        $count = $cart ? $cart->items()->sum('quantity') : 0;
        
        return response()->json(['count' => $count]);
    }
}