@extends('layouts.app')

@section('content')

<nav class="w-100 d-flex justify-content-center m-4">
</nav>

<div class="container w-100 d-flex justify-content-center mx-auto py-8" id="orders-container">
    <table class="table table-striped table-order-details">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Product Price</th>
                <th>Total Price</th>
                <th>Order Status</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <a href="{{ route('products.show', $order->product->id) }}">
                        {{ $order->product->name }}
                    </a>
                </td>
                <td>{{ $order->quantity }}</td>
                <td>${{  number_format($order->product->price, 2) }}</td>  
                <td>${{ $order->product->price * $order->quantity }}</td>
                <td>{{ $order->status }}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="w-100 d-flex justify-content-center gap-5 mt-5">
    <form action="{{ route('orders.changeStatus') }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $order->id }}">
        <input type="hidden" name="status" value="processing">
        <button type="submit" class="btn btn-primary">Processing</button>
    </form>
    
    <form action="{{ route('orders.changeStatus') }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $order->id }}">
        <input type="hidden" name="status" value="shipped">
        <button type="submit" class="btn btn-success">Shipped</button>
    </form>

    <form action="{{ route('orders.changeStatus') }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $order->id }}">
        <input type="hidden" name="status" value="completed">
        <button type="submit" class="btn btn-info">Completed</button>
    </form>

    <form action="{{ route('orders.changeStatus') }}" method="POST" class="d-inline">
        @csrf
        @method('PUT')
        <input type="hidden" name="id" value="{{ $order->id }}">
        <input type="hidden" name="status" value="cancelled">
        <button type="submit" class="btn btn-danger">Cancelled</button>
    </form>
</div>

@endsection
