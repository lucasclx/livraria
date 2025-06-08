<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use App\Services\ShippingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private ShippingService $shippingService
    ) {
        $this->middleware('auth');
    }

    public function index()
    {
        $cart = $this->cartService->getOrCreateCart();
        
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')
                ->with('error', 'Seu carrinho está vazio.');
        }

        // Validar estoque
        $stockErrors = $this->cartService->validateCartStock();
        if (!empty($stockErrors)) {
            return redirect()->route('cart.index')
                ->with('error', 'Alguns itens do seu carrinho não estão mais disponíveis: ' . implode(', ', $stockErrors));
        }

        $items = $cart->items()->with('livro')->get();
        $subtotal = $this->cartService->getCartTotal();
        
        // Calcular frete (usando CEP padrão ou do usuário)
        $userAddress = Auth::user()->addresses()->where('is_default', true)->first();
        $cep = $userAddress ? $userAddress->postal_code : '01001-000';
        
        $shipping = $this->shippingService->calculate($items, $cep);
        $total = $subtotal + $shipping['price'];

        return view('checkout.index', compact('items', 'subtotal', 'shipping', 'total'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'shipping_address' => 'required|array',
            'shipping_address.recipient_name' => 'required|string|max:100',
            'shipping_address.street' => 'required|string|max:255',
            'shipping_address.number' => 'required|string|max:20',
            'shipping_address.neighborhood' => 'required|string|max:100',
            'shipping_address.city' => 'required|string|max:100',
            'shipping_address.state' => 'required|string|max:2',
            'shipping_address.postal_code' => 'required|string|max:10',
            'payment_method' => 'required|in:credit_card,debit_card,pix,boleto',
            'save_address' => 'boolean',
            'notes' => 'nullable|string|max:500'
        ]);

        return DB::transaction(function () use ($request) {
            $cart = $this->cartService->getOrCreateCart();
            
            // Validações finais
            $stockErrors = $this->cartService->validateCartStock();
            if (!empty($stockErrors)) {
                throw new \Exception('Estoque insuficiente: ' . implode(', ', $stockErrors));
            }

            // Reservar estoque
            $this->cartService->reserveStock($cart->id);

            try {
                // Calcular valores
                $subtotal = $this->cartService->getCartTotal();
                $shipping = $this->shippingService->calculate($cart->items, $request->input('shipping_address.postal_code'));
                $total = $subtotal + $shipping['price'];

                // Criar pedido
                $order = $this->orderService->create([
                    'cart_id' => $cart->id,
                    'user_id' => Auth::id(),
                    'subtotal' => $subtotal,
                    'shipping_cost' => $shipping['price'],
                    'total' => $total,
                    'shipping_address' => $request->input('shipping_address'),
                    'payment_method' => $request->input('payment_method'),
                    'notes' => $request->input('notes'),
                    'shipping_option' => $shipping['option']
                ]);

                // Salvar endereço se solicitado
                if ($request->boolean('save_address')) {
                    Auth::user()->addresses()->create(array_merge(
                        $request->input('shipping_address'),
                        ['label' => 'Endereço de entrega']
                    ));
                }

                // Processar pagamento (simulado)
                $paymentSuccess = $this->processPayment($order, $request->input('payment_method'));
                
                if ($paymentSuccess) {
                    $this->orderService->confirmPayment($order);
                    
                    // Marcar carrinho como usado
                    $cart->update(['status' => 'completed']);
                    
                    return redirect()->route('checkout.success', $order)
                        ->with('success', 'Pedido realizado com sucesso!');
                } else {
                    // Liberar estoque se pagamento falhou
                    $this->cartService->releaseStock($cart->id);
                    $this->orderService->cancel($order, 'Falha no pagamento');
                    
                    throw new \Exception('Falha no processamento do pagamento. Tente novamente.');
                }

            } catch (\Exception $e) {
                // Liberar estoque em caso de erro
                $this->cartService->releaseStock($cart->id);
                throw $e;
            }
        });
    }

    public function success(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        return view('checkout.success', compact('order'));
    }

    public function calculateShipping(Request $request)
    {
        $request->validate([
            'postal_code' => 'required|string'
        ]);

        $cart = $this->cartService->getOrCreateCart();
        $shipping = $this->shippingService->calculate($cart->items, $request->postal_code);

        return response()->json($shipping);
    }

    private function processPayment(Order $order, string $method)
    {
        // Simulação de processamento de pagamento
        switch ($method) {
            case 'pix':
                // PIX é instantâneo
                return true;
            case 'credit_card':
            case 'debit_card':
                // Simular sucesso/falha (90% sucesso)
                return random_int(1, 10) <= 9;
            case 'boleto':
                // Boleto fica pendente
                return true;
            default:
                return false;
        }
    }
}