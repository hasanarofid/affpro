@extends('admin.layouts.app')
@section('title', 'Tambah Voucher')
@section('page-title', 'Tambah Voucher')
@section('content')
    <form action="{{ route('admin.vouchers.store') }}" method="POST">
        @csrf
        @include('admin.vouchers._form')
    </form>
@endsection