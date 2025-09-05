<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentReceipt extends Model
{
    protected $fillable = ['payment_id','file_path','printed_at'];

    protected $casts = [ 'printed_at' => 'datetime' ];

    public function payment(): BelongsTo { return $this->belongsTo(Payment::class); }
}
