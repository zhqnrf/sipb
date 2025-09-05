<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = ['bill_id','amount','paid_at','receipt_no'];

    protected $casts = [ 'paid_at' => 'datetime' ];

    public function bill(): BelongsTo { return $this->belongsTo(Bill::class); }
    public function receipt(): HasOne { return $this->hasOne(PaymentReceipt::class); }

}
