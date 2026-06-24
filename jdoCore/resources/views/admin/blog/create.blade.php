@extends('admin.layouts.app')
@section('title', 'Tulis Artikel')
@section('page-title', 'Tulis Artikel Baru')
@section('content')
    <form action="{{ route('admin.blog.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @include('admin.blog._form')
    </form>
@endsection