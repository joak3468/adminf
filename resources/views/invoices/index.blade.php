@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <h1 class="mb-4">Listado de Facturas</h1>

    <!-- Filtro de facturas -->
    <form method="GET" action="{{ route('invoices.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-2 mb-1">
                <input type="date" name="date_from" id="date_from" class="form-control" placeholder="Desde" value="{{ $date_from }}">
            </div>
            <div class="col-md-2 mb-1">
                <input type="date" name="date_to" id="date_to" class="form-control" placeholder="Hasta" value="{{ $date_to }}">
            </div>
            <div class="col-md-4 mb-2">
                <button type="button" class="btn btn-secondary" onclick="setToday()">Hoy</button>
                <button type="button" class="btn btn-secondary" onclick="setWeek()">Ultima semana</button>
                <button type="button" class="btn btn-secondary" onclick="setOneMonthToToday()">Ultimo mes</button>
                <button type="button" class="btn btn-secondary" onclick="setLastYear()">Último año</button>
            </div>
            <div class="col-md-2 mb-1">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2 mb-1">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="in_procesos" name="status[]" value="0" {{ in_array(0, $enabled_statuses) ? 'checked' : '' }}>
                    <label class="form-check-label" for="in_procesos">En Proceso</label>
                </div>
            </div>
            <div class="col-md-2 mb-1">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="created_sent" name="status[]" value="1" {{ in_array(1, $enabled_statuses) ? 'checked' : '' }}>
                    <label class="form-check-label" for="created_sent">Creada/Enviada</label>
                </div>
            </div>
            <div class="col-md-2 mb-1">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="paid" name="status[]" value="3" {{ in_array(3, $enabled_statuses) ? 'checked' : '' }}>
                    <label class="form-check-label" for="paid">Pagada</label>
                </div>
            </div>
        </div>
    </form>

    <div class="mb-4">
        <input type="text" id="searchInvoices" class="form-control" placeholder="Buscar por cliente">
    </div>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cliente</th>
                <th>Precio</th>
                <th>Fecha de creación</th>
                <th>Fecha de pago</th>
                <th>Metodo</th>
                <th>Tipo</th>
                <th>Actualizar estado</th>
            </tr>
        </thead>
        <tbody id="invoiceTable">
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->getNameStatus() }}</td>
                <td>{{ $invoice->client->name }}</td>
                <td>{{ $invoice->price }}</td>
                <td>{{ date("Y-m-d", strtotime($invoice->created_at)) }}</td>
                <td>{{ $invoice->payment_date ? : '-'  }}</td>
                <td>{{ $invoice->payment_date ? $invoice->getNamePaymentMethod() : '-'  }}</td>
                <td>{{ $invoice->getNameType()  }}</td>
                <td>
                    <form id="invoiceForm-{{ $invoice->id }}" action="{{ route('invoices.update', $invoice) }}" method="POST">
                        @csrf
                        @method('PUT')
                        @if(auth()->id() == 1  && $invoice->status > 0)
                            <button type="submit" name="newStatus" value="{{ $invoice->status - 1 }}" class="btn btn-sm btn-danger">Retroceder</button>
                        @endif
                        @if($invoice->status < 2)
                            <button type="submit" name="newStatus" value="{{ $invoice->status + 1 }}" class="btn btn-sm btn-success">Avanzar</button>
                        @endif
                        @if($invoice->status == 2)
                            <button type="button" class="btn btn-sm btn-success open-payment-modal" data-bs-toggle="modal" data-bs-target="#paymentModal" data-invoice-id="{{ $invoice->id }}" data-client-name="{{ $invoice->client->name }}" data-created-at="{{ date('Y-m-d', strtotime($invoice->created_at)) }}">Avanzar</button>
                        @endif
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentModalLabel">Confirmar Fecha de Pago</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="paymentForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <div>
                            <label class="form-label">Metodo de pago</label>
                        </div>
                        <input class="form-check-input" type="radio" name="method" value="0" id="flexRadioDefault1" checked>
                        <label class="form-check-label" for="flexRadioDefault1">
                            Transferencia
                        </label>
                        <input class="form-check-input" type="radio" name="method" value="1" id="flexRadioDefault2">
                        <label class="form-check-label" for="flexRadioDefault2">
                            Efectivo
                        </label>
                    </div>
                    <div class="mb-3">
                        <label for="paymentDate" class="form-label">Fecha de Pago</label>
                        <input type="date" class="form-control" id="paymentDate" name="paymentDate" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirmPayment">Confirmar</button>
            </div>
        </div>
    </div>
</div>


<component is="script">
function setToday() {
    let today = new Date().toISOString().split('T')[0];
    document.getElementById('date_from').value = today;
    document.getElementById('date_to').value = today;
}
function setWeek() {
    let today = new Date();
    let lastWeek = new Date();
    lastWeek.setDate(today.getDate() - 7);
    document.getElementById('date_from').value = lastWeek.toISOString().split('T')[0];
    document.getElementById('date_to').value = today.toISOString().split('T')[0];
}


function setOneMonthToToday() {
    let today = new Date();
    let lastMonth = new Date();
    lastMonth.setMonth(today.getMonth() - 1);
    
    document.getElementById('date_from').value = lastMonth.toISOString().split('T')[0];
    document.getElementById('date_to').value = today.toISOString().split('T')[0];
}

function setLastYear() {
    let today = new Date();
    let lastYear = new Date();
    lastYear.setFullYear(today.getFullYear() - 1);
    
    document.getElementById('date_from').value = lastYear.toISOString().split('T')[0];
    document.getElementById('date_to').value = today.toISOString().split('T')[0];
}

document.getElementById('searchInvoices').addEventListener('keyup', function() {
    let filter = this.value.toUpperCase();
    let rows = document.getElementById('invoiceTable').getElementsByTagName('tr');

    for (let i = 0; i < rows.length; i++) {
        let tdClient = rows[i].getElementsByTagName('td')[1];
        if (tdClient) {
            let txtValueClient = tdClient.textContent || tdClient.innerText;
            if (txtValueClient.toUpperCase().indexOf(filter) > -1) {
                rows[i].style.display = "";
            } else {
                rows[i].style.display = "none";
            }
        }       
    }
});
document.querySelectorAll('.open-payment-modal').forEach(button => {
    button.addEventListener('click', function () {
        let invoiceId = this.getAttribute('data-invoice-id');
        let clientName = this.getAttribute('data-client-name');
        let createdAt = this.getAttribute('data-created-at');
        document.getElementById('paymentModalLabel').textContent = `Confirmar Fecha de Pago para ${clientName}`;
        let form = document.getElementById('paymentForm');
        form.action = `{{ route('invoices.update', ':id') }}`.replace(':id', invoiceId);
    });
});

document.getElementById('confirmPayment').addEventListener('click', function () {
    document.getElementById('paymentForm').submit();
});
</component>

@endsection
