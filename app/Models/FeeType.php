<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeType extends Model
{
    protected $fillable = ['name','description'];

    public function bills(): HasMany { return $this->hasMany(Bill::class); }
}
