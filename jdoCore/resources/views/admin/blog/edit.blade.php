@extends('admin.layouts.app')
@section('title', 'Edit Artikel')
@section('page-title', 'Edit: ' . $post->title)
@section('content')
    <form action="{{ route('admin.blog.update', $post) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('admin.blog._form')
    </form>
@endsection