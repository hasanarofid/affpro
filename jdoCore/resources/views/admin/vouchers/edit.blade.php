@extends('admin.layouts.app')
@section('title', 'Edit Voucher')
@section('page-title', 'Edit Voucher: ' . $voucher->code)
@section('content')
    <form action="{{ route('admin.vouchers.update', $voucher) }}" method="POST">
        @csrf @method('PUT')
        @include('admin.vouchers._form')
    </form>
@endsection