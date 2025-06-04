@extends('layouts.app')
@section('title', 'Checkout')

@section('content')
<h1 class="mb-4">Confirmação de Pedido</h1>
@if($items->count())
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Livro</th>
                <th>Qtd</th>
                <th class="text-end">Subtotal</th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->livro->titulo }}</td>
                <td>{{ $item->quantity }}</td>
                <td class="text-end">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="2" class="text-end">Total</th>
                <th class="text-end">R$ {{ number_format($total, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
    <form method="POST" action="{{ route('checkout.process') }}">
        @csrf
        <button class="btn btn-gold">Confirmar Pedido</button>
    </form>
@else
    <p>Nenhum item no carrinho.</p>
@endif
@endsection
