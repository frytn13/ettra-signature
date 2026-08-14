@extends('layouts.admin')
@section('title','Edit Promosi')
@section('eyebrow','Penjualan')
@section('page-title','Edit Promosi')
@section('content')
<section class="dashboard-heading category-form-heading"><div><p class="dashboard-heading__eyebrow">Promosi</p><h2>{{ $promotion->name }}</h2><p>Perbarui nilai, target, periode, atau status promosi.</p></div></section>
<form action="{{ route('admin.promotions.update',$promotion) }}" method="POST" class="modal-friendly-form">@csrf @method('PUT') @include('admin.promotions.partials.form',['promotion'=>$promotion,'products'=>$products,'categories'=>$categories,'submitLabel'=>'Simpan Perubahan'])</form>
@endsection
