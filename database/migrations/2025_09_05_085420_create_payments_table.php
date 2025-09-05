<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('bill_id')->constrained()->cascadeOnDelete();
            $t->unsignedBigInteger('amount');     // nominal angsuran
            $t->dateTime('paid_at');
            $t->string('receipt_no')->unique();   // nomor kwitansi
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
