@extends('layouts.app')

@section('content')

<nav class="w-100 d-flex justify-content-center m-4">
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
</nav>

<div class="container w-100 d-flex justify-content-center mx-auto py-8" id="orders-container">
    @if ($orders->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Product Price</th>
                    <th>Total Price</th>
                    <th>Order Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $order)
                    <tr>
                        <td>
                            <a href="{{route('orders.show', $order->id)}}">
                                {{$order->id}}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('products.show', $order->product->id) }}">
                                {{ $order->product->name }}
                            </a>
                        </td>
                        <td>{{ $order->quantity }}</td>
                        <td>${{  number_format($order->product->price, 2) }}</td>  
                        <td>${{ $order->product->price * $order->quantity }}</td>
                        <td>{{ $order->status}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No orders found.</p>
    @endif
</div>

<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        {{ $orders->links('pagination::bootstrap-4') }}
    </ul>
</nav>

@endsection
