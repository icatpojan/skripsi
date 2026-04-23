<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWasteTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('waste_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama jenis sampah
            $table->string('description')->nullable(); // Deskripsi jenis sampah
            $table->string('icon')->nullable(); // Icon untuk jenis sampah
            $table->string('color')->default('#28a745'); // Warna untuk jenis sampah
            $table->boolean('is_active')->default(true); // Status aktif/nonaktif
            $table->timestamps();

            // Index untuk optimasi
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('waste_types');
    }
}
