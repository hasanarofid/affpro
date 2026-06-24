@extends('admin.layouts.app')
@section('title', 'Edit Produk')
@section('page-title', 'Edit Produk — ' . $product->name)

@section('content')
    <form action="{{ route('admin.products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.products._form', ['product' => $product])
    </form>
@endsection