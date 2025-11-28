<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->time('time_in')->nullable();
            $table->time('time_out')->nullable();
            $table->string('photo_in')->nullable();
            $table->string('photo_out')->nullable();
            $table->string('lat_long_in')->nullable();
            $table->string('lat_long_out')->nullable();
            $table->enum('status_masuk', ['Tepat Waktu', 'Terlambat'])->default('Tepat Waktu');
            $table->enum('status_pulang', ['Pulang Cepat', 'Tepat Waktu'])->default('Pulang Cepat');
            $table->timestamps();
            
            // Unique constraint untuk menghindari duplikasi absensi di tanggal yang sama
            $table->unique(['user_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};