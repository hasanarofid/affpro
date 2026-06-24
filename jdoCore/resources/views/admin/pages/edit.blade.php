@extends('admin.layouts.app')
@section('title', 'Edit Halaman')
@section('page-title', 'Edit: ' . $page->title)
@section('content')
    <form action="{{ route('admin.pages.update', $page) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.pages._form')
    </form>
@endsection