<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
        public function up()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('classroom_id')->nullable()->after('nis')->constrained('classrooms')->nullOnDelete();
            // opsional: sinkronkan name ke kolom lama 'kelas' kalau ingin pertahankan teks
        });
    }

    public function down()
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('classroom_id');
        });
    }
};
