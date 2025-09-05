<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->string('nis')->unique();
            $t->string('name');
            $t->string('kelas')->nullable();     // ex: X IPA 1
            $t->string('rombel')->nullable();    // opsional
            $t->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('students'); }
};
