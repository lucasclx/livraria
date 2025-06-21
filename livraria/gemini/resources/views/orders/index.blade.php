@extends('layouts.app')
@section('title', 'Meus Pedidos')

@section('content')
<h1 class="mb-4">Meus Pedidos</h1>
@if($orders->count())
    <table class="table table-bordered bg-white">
        <thead class="table-light">
            <tr>
                <th>Data</th>
                <th class="text-end">Total</th>
                <th>Status</th>
                <th>Endereço de Entrega</th>
            </tr>
        </thead>
        <tbody>
        @foreach($orders as $order)
            <tr>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td class="text-end">R$ {{ number_format($order->total, 2, ',', '.') }}</td>
                <td>{{ ucfirst($order->status) }}</td>
                <td>{{ $order->shipping_address ?? '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>Você ainda não realizou pedidos.</p>
@endif
@endsection
