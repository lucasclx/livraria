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
        $items = $cart?->items()->with('livro.categoria')->get() ?? collect();
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
                        'price' => $livro->preco_final,
                    ]);
                }
            });

            return redirect()->back()->with('success', 'Livro adicionado ao carrinho!');
            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
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
        
        // Validar estoque de todos os itens
        $stockErrors = $this->validateCartStock($cart);
        if (!empty($stockErrors)) {
            return redirect()->route('cart.index')->with('error', 
                'Alguns itens não estão mais disponíveis: ' . implode(', ', $stockErrors));
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
            return DB::transaction(function() use ($cart, $data) {
                $items = $cart->items()->with('livro')->get();
                
                // Validar estoque novamente
                $stockErrors = $this->validateCartStock($cart);
                if (!empty($stockErrors)) {
                    throw new \Exception('Estoque insuficiente: ' . implode(', ', $stockErrors));
                }

                // Reservar estoque
                foreach ($items as $item) {
                    if (!$item->livro->diminuirEstoque($item->quantity, 'reserva_venda')) {
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
                    'status' => 'pending_payment',
                ]));

                // Processar pagamento (simulado)
                $paymentSuccess = $this->processPayment($order, $data['payment_method']);
                
                if ($paymentSuccess) {
                    // Confirmar venda - estoque já foi diminuído
                    foreach ($items as $item) {
                        $item->livro->adicionarVenda($item->quantity);
                    }
                    
                    $order->update(['status' => 'confirmed']);
                    $cart->update(['status' => 'completed', 'user_id' => auth()->id()]);
                    
                    session()->forget('cart_id');
                    
                    return redirect()->route('orders.index')->with('success', 'Pedido realizado com sucesso!');
                } else {
                    // Reverter estoque em caso de falha no pagamento
                    foreach ($items as $item) {
                        $item->livro->aumentarEstoque($item->quantity, 'cancelamento');
                    }
                    
                    $order->update(['status' => 'payment_failed']);
                    throw new \Exception('Falha no pagamento. Tente novamente.');
                }
            });
            
        } catch (\Exception $e) {
            Log::error('Erro no checkout: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
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
            'user_id' => auth()->id(),
            'status' => 'active',
        ]);
        session(['cart_id' => $cart->id]);

        return $cart;
    }

    private function validateCartStock(Cart $cart): array
    {
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

    private function getSugestoes($items)
    {
        if ($items->isEmpty()) {
            return collect();
        }

        // Buscar livros da mesma categoria dos itens no carrinho
        $categorias = $items->pluck('livro.categoria_id')->filter()->unique();
        
        return Livro::whereIn('categoria_id', $categorias)
            ->whereNotIn('id', $items->pluck('livro.id'))
            ->where('ativo', true)
            ->where('estoque', '>', 0)
            ->inRandomOrder()
            ->limit(4)
            ->get();
    }

    private function calculateShipping($items)
    {
        $totalWeight = $items->sum(function($item) {
            return ($item->livro->peso ?? 0.5) * $item->quantity;
        });

        if ($totalWeight <= 1) {
            return 12.50; // PAC
        } elseif ($totalWeight <= 3) {
            return 18.90; // SEDEX
        } else {
            return 25.00; // Frete especial
        }
    }

    private function processPayment(Order $order, string $method): bool
    {
        // Simulação de processamento de pagamento
        switch ($method) {
            case 'pix':
                // PIX é instantâneo - 95% sucesso
                return random_int(1, 100) <= 95;
            case 'credit_card':
            case 'debit_card':
                // Cartões - 90% sucesso
                return random_int(1, 100) <= 90;
            case 'boleto':
                // Boleto sempre aprovado (pagamento pendente)
                return true;
            default:
                return false;
        }
    }

    public function getCartCount()
    {
        $cart = $this->getCart();
        $count = $cart ? $cart->items()->sum('quantity') : 0;
        
        return response()->json(['count' => $count]);
    }
}