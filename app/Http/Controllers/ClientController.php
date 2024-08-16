<?php

namespace App\Http\Controllers;
use App\Models\Client;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Afip;
class ClientController extends Controller
{

    public function __construct() {
        $this->middleware('auth');
    }

    public function index(Request $request) {
        $clients = Client::with('invoices')->get();
        $userId = auth()->id();
        return view('clients.index', compact('clients'));
    }


    public function create() {
        return view('clients.create');
    }


    public function store(Request $request) {
        $request->validate([
            'name' => 'required|string|max:50',
            'cuil' => 'required|string|max:50',
            'iva' => 'required|string|max:100',
            'email' => 'required|string|max:150',
            'address' => 'required|string|max:150',
            'sale_condition' => 'required|string|max:100',
            'price' => 'required|integer|min:0|max:20000000',
            'message' => 'nullable|string|max:350',
        ]);
    
        $client = Client::create([
            'user_id' => Auth::id(),
            'name' => $request->name,
            'cuil' => $request->cuil,
            'iva' => $request->iva,
            'address' => $request->address,
            'email' => $request->email,
            'sale_condition' => $request->sale_condition,
            'price' => $request->price,
            'message' => $request->message,
        ]);
        return redirect()->route('clients.index');
    }

    public function edit(Client $client) {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client) {
        $request->validate([
            'name' => 'required|string|max:50',
            'cuil' => 'required|string|max:50',
            'iva' => 'required|string|max:100',
            'address' => 'required|string|max:150',
            'email' => 'required|string|max:150',
            'sale_condition' => 'required|string|max:100',
            'price' => 'required|string|max:100',
            'message' => 'nullable|string|max:350',
        ]);
        
        $client->update([
            'name' => $request->name,
            'cuil' => $request->cuil,
            'iva' => $request->iva,
            'email' => $request->email,
            'address' => $request->address,
            'sale_condition' => $request->sale_condition,
            'price' => $request->price,
            'message' => $request->message,
        ]);
        return redirect()->route('clients.index');
    }

    public function destroy(Client $client)
    {
        $client->delete();
        return redirect()->route('clients.index');
    }




    public function test(){
        return 10;
    }
}
