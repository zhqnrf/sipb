<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    protected $fillable = [
        'student_id','fee_type_id','period','amount','paid_amount','status'
    ];

    public function student(): BelongsTo { return $this->belongsTo(Student::class); }
    public function feeType(): BelongsTo { return $this->belongsTo(FeeType::class); }
    public function payments(): HasMany { return $this->hasMany(Payment::class); }

    public function refreshStatus(): void
    {
        if ($this->paid_amount >= $this->amount) $this->status = 'Lunas';
        elseif ($this->paid_amount > 0)          $this->status = 'Sebagian';
        else                                      $this->status = 'Belum';
        $this->save();
    }

    public function remaining(): int { return max(0, $this->amount - $this->paid_amount); }
}
