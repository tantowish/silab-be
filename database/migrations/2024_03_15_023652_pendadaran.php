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
        Schema::create('pendadaran', function (Blueprint $table) {
            $table->id();
            $table->foreign(['id_students'])->references(['id'])->on('students')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['id_lecturers'])->references(['id'])->on('lecturers')->onUpdate('no action')->onDelete('cascade');
            $table->date('tanggal_sidang');
            $table->time('jam');
            $table->string('ruang');
            $table->integer('nilai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pendadaran');
    }
};