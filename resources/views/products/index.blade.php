@extends('layouts.app')

@section('content')
<div class="product-container container">
    <a class="btn btn-primary mb-3" href="{{route('products.create')}}">
        add Product
    </a>
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
      @endif
    @if ($products->count() > 0)
        <div class="row">
            @foreach ($products as $product)
                <div class="col-md-3 mb-4">
                    <div class="card product-card">
                        <img src="{{$product->product_url[0]}}" alt="{{ $product->name }}">
                        <a href="{{ route('products.show', $product->id) }}">{{ $product->name }}</a>
                        <p class="category">Category: {{ $product->category->name }}</p>
                        <p class="price">${{ $product->price }}</p>
                        <p>Quantity: {{ $product->quantity }}</p>
                    </div>
                </div>
            @endforeach
        </div>

    @else
        <p>No products found.</p>
    @endif
</div>
<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        {{ $products->links('pagination::bootstrap-4') }}
    </ul>
</nav>
@endsection