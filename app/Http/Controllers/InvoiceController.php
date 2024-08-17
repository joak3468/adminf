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
        $enabled_statuses = isset($request->status) ? $request->status : [0,1,3];
        if(in_array(1, $enabled_statuses))
            $enabled_statuses[] = 2;
        $invoices = Invoice::query()
        ->orderBy('created_at', 'desc')
        ->whereBetween('created_at', [$date_from, date("Y-m-d", strtotime("+1 day", strtotime($date_to)))])
        ->when(isset($request->status), function($q) use ($enabled_statuses)  {
            return $q->whereIn("status", $enabled_statuses);
        })
        ->paginate(15)
        ->appends($request->except('page'));
        return view('invoices.index', compact('invoices', 'date_from', 'date_to', 'enabled_statuses'));
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
        $invoice->update($data);
    
        return redirect()->route('invoices.index');
    }
    

    public function destroy(Invoice $invoice) {
        $invoice->delete();
        return redirect()->route('clients.index');
    }
}
