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
        <div class="row mb-3">
            <div class="col-md-6">
                <label class="form-label">Rua</label>
                <input type="text" name="street" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Cidade</label>
                <input type="text" name="city" class="form-control" required>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-md-4">
                <label class="form-label">Estado</label>
                <input type="text" name="state" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">CEP</label>
                <input type="text" name="zip" class="form-control" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">País</label>
                <input type="text" name="country" class="form-control" required>
            </div>
        </div>
        <button class="btn btn-gold">Confirmar Pedido</button>
    </form>
@else
    <p>Nenhum item no carrinho.</p>
@endif
@endsection
