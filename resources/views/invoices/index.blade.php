@extends('layouts.app')

@section('content')
<style>
    body {
        position: relative;
        min-height: 100vh;
    }
    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-image: url('https://www.reddit.com/media?url=https%3A%2F%2Fpreview.redd.it%2Fanyone-else-curious-to-how-the-statue-of-liberty-moves-from-v0-1lhqms93bl191.jpg%3Fwidth%3D618%26format%3Dpjpg%26auto%3Dwebp%26s%3D17f48acefd8089f7380143177f607c308db70a13');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        background-repeat: no-repeat;
        opacity: 0.35;
        z-index: 0;
        pointer-events: none;
    }
    #app {
        position: relative;
        z-index: 1;
    }
    main {
        background-color: transparent !important;
        position: relative;
        z-index: 1;
    }
    .invoices-content-wrapper {
        background-color: rgba(255, 255, 255, 0.85);
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin: 20px auto;
        position: relative;
        z-index: 1;
    }
</style>

<div class="container mt-5">
<div class="invoices-content-wrapper">
    <h1 class="mb-4">Listado de Facturas</h1>

    <!-- Filtro de facturas -->
    <form method="GET" action="{{ route('invoices.index') }}" class="mb-4">
        <div class="row">
            <div class="col-md-2 mb-1">
                <input type="date" name="date_from" id="date_from" class="form-control" placeholder="Desde" value="{{ $date_from }}">
                <!-- DEBUG: date_from value = {{ $date_from }} -->
            </div>
            <div class="col-md-2 mb-1">
                <input type="date" name="date_to" id="date_to" class="form-control" placeholder="Hasta" value="{{ $date_to }}">
                <!-- DEBUG: date_to value = {{ $date_to }} -->
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

    <form method="GET" action="{{ route('invoices.index') }}" class="mb-4" id="clientSearchForm">
        <div class="row">
            <div class="col-md-8 mb-2">
                <input type="text" name="client_search" id="client_search" class="form-control" placeholder="Buscar por nombre o CUIL del cliente" value="{{ $client_search ?? '' }}">
            </div>
            <div class="col-md-2 mb-2">
                <button type="submit" class="btn btn-primary" id="searchButton">Buscar</button>
            </div>
            @if(isset($client_search) && $client_search)
            <div class="col-md-2 mb-2">
                <a href="{{ route('invoices.index', array_merge(request()->except(['client_search', 'page']))) }}" class="btn btn-secondary">Limpiar búsqueda</a>
            </div>
            @endif
        </div>
    </form>

<div class="table-responsive">
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Estado</th>
                <th>Cliente</th>
                <th>CUIL</th>
                <th>Direccion</th>
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
                <td>{{ $invoice->client->cuil }}</td>
                <td>{{ $invoice->client->address }}</td>
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

@if(isset($client_search) && $client_search)
    <div class="alert alert-info mt-3">
        Mostrando todos los resultados para la búsqueda "{{ $client_search }}". Total: {{ count($invoices) }} factura(s).
    </div>
@else
    {{ $invoices->links('pagination::bootstrap-4') }}
@endif

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


<script type="text/javascript">
console.log('=== SCRIPT TAG NORMAL INICIADO ===');
console.log('Document ready state:', document.readyState);

function setToday() {
    console.log('setToday() llamado');
    let today = new Date().toISOString().split('T')[0];
    console.log('Fecha hoy:', today);
    document.getElementById('date_from').value = today;
    document.getElementById('date_to').value = today;
    console.log('Valores actualizados');
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

console.log('Antes de agregar event listeners');

document.getElementById('confirmPayment').addEventListener('click', function () {
    console.log('confirmPayment clickeado');
    document.getElementById('paymentForm').submit();
});

console.log('confirmPayment listener agregado');

// Configurar el formulario de búsqueda para que incluya las fechas actuales
console.log('Configurando searchForm...');

// Intentar sin DOMContentLoaded primero
console.log('=== INTENTO 1: SIN DOMContentLoaded ===');
let searchFormDirect = document.getElementById('clientSearchForm');
let searchButtonDirect = document.getElementById('searchButton');
console.log('searchFormDirect:', searchFormDirect);
console.log('searchButtonDirect:', searchButtonDirect);

if (searchFormDirect && searchButtonDirect) {
    console.log('Elementos encontrados directamente, agregando listener...');
    searchButtonDirect.addEventListener('click', function(e) {
        console.log('=== BOTÓN BUSCAR CLICKEADO (directo) ===');
        e.preventDefault();
        handleSearchForm(e, this);
    });
    console.log('Listener directo agregado');
} else {
    console.log('Elementos NO encontrados directamente, esperando DOMContentLoaded...');
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOMContentLoaded EJECUTADO ===');
    
    let searchForm = document.getElementById('clientSearchForm');
    let searchButton = document.getElementById('searchButton');
    
    console.log('searchForm:', searchForm);
    console.log('searchButton:', searchButton);
    
    if (searchForm && searchButton) {
        console.log('Agregando listener con DOMContentLoaded...');
        searchButton.addEventListener('click', function(e) {
            console.log('=== BOTÓN BUSCAR CLICKEADO (DOMContentLoaded) ===');
            e.preventDefault();
            handleSearchForm(e, this);
        });
        console.log('Listener agregado con DOMContentLoaded');
    } else {
        console.log('ERROR: No se encontraron searchForm o searchButton en DOMContentLoaded');
        console.log('Buscando nuevamente en 100ms...');
        setTimeout(function() {
            let retryForm = document.getElementById('clientSearchForm');
            let retryButton = document.getElementById('searchButton');
            console.log('Retry - searchForm:', retryForm);
            console.log('Retry - searchButton:', retryButton);
            if (retryForm && retryButton) {
                retryButton.addEventListener('click', function(e) {
                    console.log('=== BOTÓN BUSCAR CLICKEADO (retry) ===');
                    e.preventDefault();
                    handleSearchForm(e, this);
                });
            }
        }, 100);
    }
});

// Función separada para manejar el envío del formulario
function handleSearchForm(e, button) {
    console.log('=== handleSearchForm INICIADO ===');
    console.log('Event:', e);
    console.log('Button:', button);
    
    let searchForm = document.getElementById('clientSearchForm');
    console.log('searchForm en handleSearchForm:', searchForm);
    
    if (!searchForm) {
        console.error('ERROR: searchForm no encontrado en handleSearchForm');
        return false;
    }
    
    // Obtener los valores actuales de los inputs de fecha del formulario de filtros
    let dateFromInput = document.getElementById('date_from');
    let dateToInput = document.getElementById('date_to');
    
    console.log('=== VALORES DE INPUTS DE FECHA ===');
    console.log('dateFromInput encontrado:', dateFromInput);
    console.log('dateToInput encontrado:', dateToInput);
    
    if (dateFromInput) {
        console.log('dateFromInput.value:', dateFromInput.value);
        console.log('dateFromInput.getAttribute("value"):', dateFromInput.getAttribute('value'));
        console.log('dateFromInput.outerHTML:', dateFromInput.outerHTML);
    } else {
        console.error('ERROR: dateFromInput no encontrado');
    }
    
    if (dateToInput) {
        console.log('dateToInput.value:', dateToInput.value);
        console.log('dateToInput.getAttribute("value"):', dateToInput.getAttribute('value'));
        console.log('dateToInput.outerHTML:', dateToInput.outerHTML);
    } else {
        console.error('ERROR: dateToInput no encontrado');
    }
    
    // Crear o actualizar los campos ocultos de fecha
    let dateFromHidden = document.getElementById('search_date_from');
    let dateToHidden = document.getElementById('search_date_to');
    
    console.log('=== CAMPOS OCULTOS ===');
    console.log('dateFromHidden encontrado:', dateFromHidden);
    console.log('dateToHidden encontrado:', dateToHidden);
    
    if (!dateFromHidden) {
        console.log('Creando campo oculto date_from...');
        dateFromHidden = document.createElement('input');
        dateFromHidden.type = 'hidden';
        dateFromHidden.name = 'date_from';
        dateFromHidden.id = 'search_date_from';
        searchForm.appendChild(dateFromHidden);
        console.log('Campo oculto date_from creado y agregado');
    }
    
    if (!dateToHidden) {
        console.log('Creando campo oculto date_to...');
        dateToHidden = document.createElement('input');
        dateToHidden.type = 'hidden';
        dateToHidden.name = 'date_to';
        dateToHidden.id = 'search_date_to';
        searchForm.appendChild(dateToHidden);
        console.log('Campo oculto date_to creado y agregado');
    }
    
    // Actualizar los valores desde los inputs de fecha del formulario de filtros
    if (dateFromInput) {
        let dateFromValue = dateFromInput.value;
        console.log('dateFromValue obtenido:', dateFromValue);
        
        if (!dateFromValue) {
            dateFromValue = dateFromInput.getAttribute('value');
            console.log('dateFromValue desde getAttribute:', dateFromValue);
        }
        
        if (dateFromValue) {
            dateFromHidden.value = dateFromValue;
            console.log('✓ dateFromHidden.value seteado a:', dateFromValue);
            console.log('Verificación - dateFromHidden.value después:', dateFromHidden.value);
        } else {
            console.error('ERROR: dateFromInput no tiene valor');
        }
    }
    
    if (dateToInput) {
        let dateToValue = dateToInput.value;
        console.log('dateToValue obtenido:', dateToValue);
        
        if (!dateToValue) {
            dateToValue = dateToInput.getAttribute('value');
            console.log('dateToValue desde getAttribute:', dateToValue);
        }
        
        if (dateToValue) {
            dateToHidden.value = dateToValue;
            console.log('✓ dateToHidden.value seteado a:', dateToValue);
            console.log('Verificación - dateToHidden.value después:', dateToHidden.value);
        } else {
            console.error('ERROR: dateToInput no tiene valor');
        }
    }
    
    // Verificar valores finales antes de enviar
    console.log('=== VALORES FINALES ANTES DE ENVIAR ===');
    console.log('dateFromHidden.value:', dateFromHidden.value);
    console.log('dateToHidden.value:', dateToHidden.value);
    console.log('dateFromHidden.outerHTML:', dateFromHidden.outerHTML);
    console.log('dateToHidden.outerHTML:', dateToHidden.outerHTML);
    
    // Obtener los checkboxes de status seleccionados
    let statusCheckboxes = document.querySelectorAll('input[name="status[]"]:checked');
    console.log('Status checkboxes seleccionados:', statusCheckboxes.length);
    
    // Eliminar los inputs de status existentes del formulario de búsqueda
    let statusInputs = searchForm.querySelectorAll('input[name="status[]"]');
    console.log('Status inputs a eliminar:', statusInputs.length);
    statusInputs.forEach(input => {
        console.log('Eliminando status input:', input.value);
        input.remove();
    });
    
    // Agregar nuevos inputs de status con los valores actuales
    statusCheckboxes.forEach(function(checkbox) {
        let hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'status[]';
        hiddenInput.value = checkbox.value;
        searchForm.appendChild(hiddenInput);
        console.log('Agregado status input:', checkbox.value);
    });
    
    // Mostrar el formulario completo antes de enviar
    console.log('=== FORMDATA COMPLETO ===');
    let formData = new FormData(searchForm);
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }
    
    // Mostrar también como URL
    let urlParams = new URLSearchParams(formData);
    console.log('URL completa:', searchForm.action + '?' + urlParams.toString());
    
    // Enviar el formulario
    console.log('=== ENVIANDO FORMULARIO ===');
    searchForm.submit();
    
    return false;
}

console.log('=== FIN DEL SCRIPT ===');
</script>

</div>
</div>

@endsection
