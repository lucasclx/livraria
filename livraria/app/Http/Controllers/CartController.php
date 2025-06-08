<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Livro;
use App\Models\Order;
use App\Services\PaymentService;
use Illuminate\Http\Request;

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

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request, Livro $livro)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $cart = $this->getOrCreateCart();

        $item = $cart->items()->where('livro_id', $livro->id)->first();
        if ($item) {
            $item->increment('quantity', $quantity);
        } else {
            $cart->items()->create([
                'livro_id' => $livro->id,
                'quantity' => $quantity,
                'price' => $livro->preco,
            ]);
        }

        return redirect()->back()->with('success', 'Livro adicionado ao carrinho!');
    }

    public function update(Request $request, CartItem $item)
    {
        $quantity = max(1, (int) $request->input('quantity', 1));
        $item->update(['quantity' => $quantity]);

        return redirect()->route('cart.index');
    }

    public function remove(CartItem $item)
    {
        $item->delete();
        return redirect()->route('cart.index');
    }

    public function checkout()
    {
        $cart = $this->getCart();
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $items = $cart->items()->with('livro')->get();
        $total = $items->sum(fn ($item) => $item->price * $item->quantity);

        return view('cart.checkout', compact('items', 'total'));
    }

    public function processCheckout(Request $request)
    {
        $cart = $this->getCart();
        if (!$cart || $cart->items()->count() === 0) {
            return redirect()->route('cart.index')->with('error', 'Seu carrinho está vazio.');
        }

        $data = $request->validate([
            'street' => 'required|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'zip' => 'required|string',
            'country' => 'required|string',
        ]);

        $total = $cart->items->sum(fn ($item) => $item->price * $item->quantity);

        $order = Order::create(array_merge($data, [
            'cart_id' => $cart->id,
            'user_id' => auth()->id(),

            'total' => $total,
            'status' => 'completed',
        ]));

        $cart->update([
            'status' => 'completed',
            'user_id' => auth()->id(),
        ]);
        session()->forget('cart_id');

        $items = $cart->items()->with('livro')->get();

        return view('orders.show', compact('order', 'items'))
            ->with('success', 'Pedido realizado com sucesso!');
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
}
