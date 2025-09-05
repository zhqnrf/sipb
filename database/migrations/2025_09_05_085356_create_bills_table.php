<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('bills', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('fee_type_id')->constrained()->cascadeOnDelete();
            $t->string('period');                 // YYYY-MM
            $t->unsignedBigInteger('amount');     // total tagihan (rupiah)
            $t->unsignedBigInteger('paid_amount')->default(0);
            $t->enum('status', ['Belum', 'Sebagian', 'Lunas'])->default('Belum');
            $t->timestamps();
            $t->unique(['student_id','fee_type_id','period']);
        });
    }
    public function down(): void { Schema::dropIfExists('bills'); }
};
