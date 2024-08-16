@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Actualizar Cliente </h1>
    <form action="{{ route('clients.update', $client) }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="name">Nombre</label>
                <input type="text" class="form-control" id="name" name="name" value="{{$client->name}}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="cuil">CUIL</label>
                <input type="text" class="form-control" id="cuil" name="cuil" value="{{$client->cuil}}" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="{{$client->email}}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="iva">IVA</label>
                <input type="text" class="form-control" id="iva" name="iva" value="{{$client->iva}}" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="address">Dirección</label>
                <input type="text" class="form-control" id="address" name="address" value="{{$client->address}}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="sale_condition">Condición de Venta</label>
                <input type="text" class="form-control" id="sale_condition" name="sale_condition" value="{{$client->sale_condition}}" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="price">Precio</label>
                <input type="number" class="form-control" id="price" name="price" value="{{$client->price}}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="message">Mensaje</label>
                <textarea class="form-control" id="message" name="message">{{$client->message}}</textarea>
            </div>
        </div>
        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Guardar</button>
        </div>
    </form>
</div>
@endsection
