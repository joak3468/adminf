<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Invoice extends Model
{
    use HasFactory;
    protected $table = 'invoices';
    protected $fillable = [ 'client_id', 'price', 'status', 'payment_date', 'type', 'payment_method'];
    const TYPES  = [0 => "Mensual", 1 => "Ocasional"];
    const STATUS_NAMES = [0 => 'Procesando', 1 => 'Creada', 2 => 'Enviada', 3 => 'Pagada'];
    const PAYMENT_METHODS = [0 => 'Transferencia', 1 => 'Efectivo'];

    public function client() : object {
        return $this->belongsTo(Client::class);
    }

    public function getNameStatus() : string {
        return self::STATUS_NAMES[$this->status];
    }

    public function getNameType() : string {
        return self::TYPES[$this->type];
    }
    public function getNamePaymentMethod() : string {
        return self::PAYMENT_METHODS[$this->payment_method];
    }

    protected static function booted() {
        static::created(function ($invoice) {
            Log::channel('invoices')->info('INVOICE ' . $invoice->id . ' CREADO por el USUARIO ' . auth()->id());
        });

        static::updated(function ($invoice) {
            $changes = $invoice->getChanges();
            $original = $invoice->getOriginal();

            Log::channel('invoices')->info('INVOICE ' . $invoice->id . ' ACTUALIZADO por el USUARIO ' . auth()->id(), [
                'cambios' => $changes,
                'original' => $original
            ]);
        });

        static::deleted(function ($invoice) {
            Log::channel('invoices')->info('INVOICE ' . $invoice->id . ' ELIMINADO por el USUARIO ' . auth()->id());
        });
    }
}
