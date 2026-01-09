<?php
namespace App\Http\Controllers;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class InvoiceController extends Controller {
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $date_from = $request->input('date_from', date("Y-m-d", strtotime("-1 month")));
        $date_to   = $request->input('date_to', date("Y-m-d"));
        $client_search = $request->input('client_search', '');
        $enabled_statuses = isset($request->status) ? $request->status : [0,1,3];
        if(in_array(1, $enabled_statuses))
            $enabled_statuses[] = 2;
        
        $query = Invoice::with('client')
            ->orderBy('created_at', 'desc')
            ->whereBetween('created_at', [$date_from, date("Y-m-d", strtotime("+1 day", strtotime($date_to)))])
            ->whereIn("status", $enabled_statuses)
            ->when($client_search, function($q) use ($client_search) {
                return $q->whereHas('client', function($clientQuery) use ($client_search) {
                    $clientQuery->where('name', 'like', '%' . $client_search . '%')
                                ->orWhere('cuil', 'like', '%' . $client_search . '%');
                });
            });
        
        // Si hay búsqueda de cliente, devolver todos los resultados sin paginación
        if ($client_search) {
            $invoices = $query->get();
        } else {
            $invoices = $query->paginate(25)->appends($request->except('page'));
        }
        
        return view('invoices.index', compact('invoices', 'date_from', 'date_to', 'enabled_statuses', 'client_search'));
    }


    public function create($clientId) {
        $client = Client::findOrFail($clientId);
        return view('invoices.create', compact('client'));
    }

    public function store(Request $request, Client $client) {
        $request->validate([ 'client_price' => 'required|numeric' ]);
        
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'price' => $request->client_price,
            'type' => $request->type,
        ]);
        return redirect()->route('invoices.index');
    }

    public function edit(Invoice $invoice) {
        return view('invoices.edit', compact('invoice'));
    }

    public function update(Request $request, Invoice $invoice) {
        $new_status = $request->has('paymentDate') ? 3 : $request->newStatus;
        $data = [
            'status' => $new_status,
        ];

        if($request->has('paymentDate')){
            $data['payment_date'] = $request->paymentDate;
            $data['payment_method'] = $request->method;
        }
        
        // Si se retrocede desde status Pagada (3) a Enviada (2), poner fecha de pago en null
        if($request->has('newStatus') && $invoice->status == 3 && $request->newStatus == 2) {
            $data['payment_date'] = null;
            $data['payment_method'] = null;
        }
        
        $invoice->update($data);
    
        return redirect()->route('invoices.index');
    }
    

    public function destroy(Invoice $invoice) {
        $invoice->delete();
        return redirect()->route('clients.index');
    }
}
