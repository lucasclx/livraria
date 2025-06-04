@extends('layouts.app')
@section('title', 'Meu Carrinho')

@section('content')
<h1 class="mb-4">Meu Carrinho</h1>
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if($items->count())
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Livro</th>
                <th class="text-end">Preço</th>
                <th style="width:150px">Quantidade</th>
                <th class="text-end">Subtotal</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach($items as $item)
            <tr>
                <td>{{ $item->livro->titulo }}</td>
                <td class="text-end">R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                <td>
                    <form method="POST" action="{{ route('cart.item.update', $item) }}" class="d-flex">
                        @csrf
                        <input type="number" name="quantity" min="1" value="{{ $item->quantity }}" class="form-control form-control-sm me-2">
                        <button class="btn btn-sm btn-primary">Atualizar</button>
                    </form>
                </td>
                <td class="text-end">R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                <td>
                    <form method="POST" action="{{ route('cart.item.remove', $item) }}">
                        @csrf
                        <button class="btn btn-sm btn-danger">Remover</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-end">Total</th>
                <th class="text-end">R$ {{ number_format($total, 2, ',', '.') }}</th>
                <th></th>
            </tr>
        </tfoot>
    </table>
    <a href="{{ route('checkout') }}" class="btn btn-gold">Finalizar Compra</a>
@else
    <p>Seu carrinho está vazio.</p>
@endif
@endsection
