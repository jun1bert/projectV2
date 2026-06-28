<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoicePayment extends Model
{
    protected $fillable = ['invoice_id', 'amount', 'payment_method', 'notes', 'received_by'];
    protected $casts = ['amount' => 'decimal:2'];

    public function invoice() { return $this->belongsTo(Invoice::class); }
    public function receiver() { return $this->belongsTo(User::class, 'received_by'); }
}
