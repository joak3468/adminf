<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Client extends Model
{
    use HasFactory;
    protected $table = 'clients';
    protected $fillable = [ 'user_id', 'name', 'cuil', 'iva', 'address', 'sale_condition', 'price', 'message', 'email', 'status' ];

    public function invoices() {
        return $this->hasMany(Invoice::class);
    }

    protected static function booted() {
        static::created(function ($client) {
            Log::channel('clients')->info('CLIENTE '. $client->id .' CREADO por el USUARIO '.auth()->id());
        });

        static::updated(function ($client) {
            $changes = $client->getChanges();
            $original = $client->getOriginal();

            Log::channel('clients')->info('CLIENTE ' . $client->id . ' ACTUALIZADO por el USUARIO ' . auth()->id(), [
                'cambios' => $changes,
                'original' => $original
            ]);
        });
        static::deleted(function ($client) {
            Log::channel('clients')->info('CLIENTE ' . $client->id . ' ELIMINADO por el USUARIO ' . auth()->id());
        });
    }
}
