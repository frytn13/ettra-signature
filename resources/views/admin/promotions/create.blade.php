@extends('layouts.admin')
@section('title','Tambah Promosi')
@section('eyebrow','Penjualan')
@section('page-title','Tambah Promosi')
@section('content')
<section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Promosi Baru</p><h2>Atur diskon</h2><p>Diskon dapat diterapkan ke semua produk, satu produk, atau satu kategori.</p></div></section>
<form action="{{ route('admin.promotions.store') }}" method="POST" class="modal-friendly-form">@csrf @include('admin.promotions.partials.form',['promotion'=>$promotion,'products'=>$products,'categories'=>$categories,'submitLabel'=>'Simpan Promosi'])</form>
@endsection
