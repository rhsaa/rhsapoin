<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pelanggars', function (Blueprint $table) {
            $table->id();
            $table->integer('id_siswa');
            $table->integer('poin_pelanngar');
            $table->integer('status_pelanggar'); // 1=15 2=20 3=30 4=40 5=50 6=100
            $table->integer('status'); // 0=tidak perlu ditindak 1=perlu ditindak 2=sudah ditindak
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelanggars');
    }
};
