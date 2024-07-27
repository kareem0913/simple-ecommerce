@extends('layouts.app')

@section('content')

<nav class="w-100 d-flex justify-content-center m-4">
    <a href="{{route('categories.create')}}"> Add Category</a>
    @if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
    @endif
</nav>

<div class="container w-100 d-flex justify-content-center mx-auto py-8" id="orders-container">
    
    @if ($categories->count() > 0)
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($categories as $category)
                    <tr>
                        <td>{{ $category->name}}</td>
                        <td>{{ $category->description}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No categories found.</p>
    @endif
</div>

<nav aria-label="Pagination">
    <ul class="pagination justify-content-center">
        {{ $categories->links('pagination::bootstrap-4') }}
    </ul>
</nav>

@endsection
