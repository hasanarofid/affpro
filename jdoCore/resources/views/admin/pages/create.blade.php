@extends('admin.layouts.app')
@section('title', 'Buat Halaman')
@section('page-title', 'Buat Halaman Baru')
@section('content')
    <form action="{{ route('admin.pages.store') }}" method="POST">
        @csrf
        @include('admin.pages._form')
    </form>
@endsection