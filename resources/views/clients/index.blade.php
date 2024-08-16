@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row">
        <div class="col-8 mb-4">
            <input type="text" id="searchClients" class="form-control" placeholder="Buscar por nombre, email o CUIL">
        </div>
        <div class="col-2 right">
            <a href="{{route('clients.create')}}" type="button" class="btn btn-outline-success">Crear nuevo cliente</a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>CUIL</th>
                    <th>IVA</th>
                    <th>Dirección</th>
                    <th>Condición de Venta</th>
                    <th>Precio</th>
                    <th>Mensaje</th>
                    <th>Email</th>
                    <th>Factura</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="clientTable">
                @foreach($clients as $client)
                <tr>
                    <td>{{ $client->name }}</td>
                    <td>{{ $client->cuil }}</td>
                    <td>{{ $client->iva }}</td>
                    <td>{{ $client->address }}</td>
                    <td>{{ $client->sale_condition }}</td>
                    <td>{{ $client->price }}</td>
                    <td style="max-width: 200px; word-wrap: break-word; white-space: normal;">
                        {{ $client->message }}
                    </td>
                    <td>{{ $client->email }}</td>
                    <td><a href="{{ route('invoices.create', $client->id) }}" class="btn btn-sm btn-success">Crear</a></td>
                    
                    <td>
                        <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-primary">Actualizar</a>
                        @if(auth()->id() == 1 )
                        <form action="{{ route('clients.destroy', $client) }}" method="POST" style="display: inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('¿Estás seguro?')">Borrar</button>
                        </form>
                        @endif
                    </td>
                   
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<component is="script">
    document.getElementById('searchClients').addEventListener('keyup', function() {
        let filter = this.value.toUpperCase();
        let rows = document.getElementById('clientTable').getElementsByTagName('tr');
        
        for (let i = 0; i < rows.length; i++) {
            let tdName = rows[i].getElementsByTagName('td')[0];
            let tdCuil = rows[i].getElementsByTagName('td')[1];
            let tdEmail = rows[i].getElementsByTagName('td')[3];
            
            if (tdName || tdCuil || tdEmail) {
                let txtValueName = tdName.textContent || tdName.innerText;
                let txtValueCuil = tdCuil.textContent || tdCuil.innerText;
                let txtValueEmail = tdEmail.textContent || tdEmail.innerText;
                
                if (txtValueName.toUpperCase().indexOf(filter) > -1 || 
                    txtValueCuil.toUpperCase().indexOf(filter) > -1 || 
                    txtValueEmail.toUpperCase().indexOf(filter) > -1) {
                    rows[i].style.display = "";
                } else {
                    rows[i].style.display = "none";
                }
            }
        }
    });
</component>
@endsection