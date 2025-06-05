@extends('layouts.app')
@section('title', 'Pedido #' . $order->id)

@section('content')
<h1 class="mb-4">Pedido Realizado</h1>
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div class="mb-4">
    <h5>Endereço de Entrega</h5>
    <p>{{ $order->street }}<br>
       {{ $order->city }}, {{ $order->state }} {{ $order->zip }}<br>
       {{ $order->country }}</p>
</div>
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
                <th class="text-end">R$ {{ number_format($order->total, 2, ',', '.') }}</th>
            </tr>
        </tfoot>
    </table>
@endif
<a href="{{ route('livros.index') }}" class="btn btn-gold">Continuar Comprando</a>
@endsection
