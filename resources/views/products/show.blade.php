@extends('layouts.app')

@section('content')
<div class="container">
    @if (session('error'))
        <div class="alert alert-danger mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="row">
        <!-- Main Product Image -->
        <div class="col-md-6">
            <div class="product-main-image mb-4">
                @if(!empty($product->product_url[0]))
                    <img id="main-image" src="{{ $product->product_url[0] }}" alt="Product Image" class="img-fluid">
                @endif
            </div>

            <!-- Thumbnail Images -->
            <div class="product-thumbnail-gallery">
                <div class="row">
                    @foreach(array_slice($product->product_url, 1) as $image)
                        <div class="col-3 mb-2">
                            <img src="{{ $image }}" alt="Product Thumbnail" class="img-thumbnail" onclick="changeMainImage('{{ $image }}')">
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Product Information -->
        <div class="col-md-6">
            <div class="product-info">
                <h1 class="product-name">{{ $product->name }}</h1>
                <p class="product-price">${{ number_format($product->price, 2) }}</p>
                <div class="rating mb-3">
                    <span class="star-rating">★★★★★</span>
                    <span class="review-count">(125 Reviews)</span>
                </div>
                <p class="product-category">Category: {{ $product->category->name }}</p>
                <p class="product-description">
                    {{ $product->description }}
                </p>

                <form method="POST" action="{{ route('orders.store') }}" class="d-flex align-items-center">
                    @csrf

                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    <input type="number" name="quantity" value="1" min="1" class="form-control me-2 @error('quantity') is-invalid @enderror" style="max-width: 80px;">

                    <button type="submit" class="btn btn-primary">Order Now</button>

                    @error('quantity')
                        <div class="invalid-feedback d-block mt-2">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror

                    @error('product_id')
                        <div class="invalid-feedback d-block mt-2">
                            <strong>{{ $message }}</strong>
                        </div>
                    @enderror
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
