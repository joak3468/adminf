@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Confirmar Factura</h1>

    <form method="POST" action="{{ route('invoices.store', $client) }}">
        @csrf
        <div class="card">
            <div class="card-body">
                <h4 class="card-title">Detalles del Cliente < {{$client->name}} ></h4>

                <div class="mb-3">
                    <label for="client_cuil" class="form-label">CUIL:</label>
                    <input type="text" class="form-control text-info" id="client_cuil" name="client_cuil" value="{{ $client->cuil }}" readonly>
                </div>

                <div class="mb-3">
                    <label for="client_email" class="form-label">Email:</label>
                    <input type="email" class="form-control text-info" id="client_email" name="client_email" value="{{ $client->email }} " readonly>
                </div>
                <div class="mb-3">
                    <label for="client_cuil" class="form-label">CUIL:</label>
                    <input type="email" class="form-control text-info" id="client_cuil" name="client_cuil" value="{{ $client->cuil }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="client_address" class="form-label">Dirección:</label>
                    <input type="text" class="form-control text-info" id="client_address" name="client_address" value="{{ $client->address }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="client_sale_condition" class="form-label">Condición de Venta:</label>
                    <input type="text" class="form-control text-info" id="client_sale_condition" name="client_sale_condition" value="{{ $client->sale_condition }}" readonly>
                </div>
                <div class="mb-3">
                    <label for="client_mensaje" class="form-label">Mensaje:</label>
                    <input type="text" class="form-control text-info" id="client_mensaje" name="client_mensaje" value="{{ $client->message }}" readonly>
                </div>    
                <h4 class="card-title">Detalles de la Factura</h4>
                <div class="mb-3">
                    <input class="form-check-input" type="radio" name="type" value="0" id="flexRadioDefault1" checked>
                    <label class="form-check-label" for="flexRadioDefault1">
                      Mensual
                    </label>
                    <input class="form-check-input" type="radio" name="type" value="1" id="flexRadioDefault2">
                    <label class="form-check-label" for="flexRadioDefault2">
                      Ocasional
                    </label>
                  </div>        
                <div class="mb-3">
                    <label for="client_price" class="form-label">Precio:</label>
                    <input type="text" class="form-control" id="client_price" name="client_price" value="{{ $client->price }}">
                </div>   
            </div>
        </div>

        <div class="mt-4 d-flex justify-content-between">
            <a href="{{ url()->previous() }}" class="btn btn-secondary">Cancelar</a>
            <button type="submit" class="btn btn-primary">Confirmar</button>
        </div>
    </form>
</div>

@endsection
