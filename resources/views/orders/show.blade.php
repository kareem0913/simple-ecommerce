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
                    <td>{{ $order->status}}</td>
                </tr>
            </tbody>
        </table>
</div>
<div class="w-100 d-flex justify-content-center gap-5 mt-5">
    <button class="btn btn-primary change-status" data-id="{{ $order->id }}" data-status="processing">Processing</button>
    <button class="btn btn-success change-status" data-id="{{ $order->id }}" data-status="shipped">Shipped</button>
    <button class="btn btn-info change-status" data-id="{{ $order->id }}" data-status="completed">Completed</button>    
    <button class="btn btn-danger change-status" data-id="{{ $order->id }}" data-status="cancelled">Cancelled</button>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    $('.change-status').on('click', function () {
        const orderId = $(this).data('id');
        const status = $(this).data('status');

        console.log(status); 
        $.ajax({
            url : 'http://localhost:8000/change-order-status',
            type : 'put',
            data : {
                id : orderId,
                status : status,
                _token : '{{ csrf_token() }}'
            },
            success: function (response) {
                if (response.success) {
                    location.reload(); 
                } else {
                    alert('Failed to update status: ' + response.message);
                }
            },
            error: function (xhr) {
                 alert('An error occurred: ' + xhr.responseText);
            }
        })
        
    });
});

</script>
@endsection
