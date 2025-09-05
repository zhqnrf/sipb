<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    
    public function classroom()
{
    return $this->belongsTo(Classroom::class);
}

protected $fillable = ['nis','name','kelas','rombel','classroom_id'];

    public function bills(): HasMany { return $this->hasMany(Bill::class); }
}
