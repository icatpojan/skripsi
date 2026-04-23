<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDistrictsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('districts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama distrik
            $table->text('description')->nullable(); // Deskripsi distrik
            $table->json('boundaries'); // Koordinat batas distrik (polygon)
            $table->string('color', 7)->default('#3b82f6'); // Warna untuk peta
            $table->boolean('is_active')->default(true); // Status aktif/tidak
            $table->timestamps();

            // Index untuk optimasi
            $table->index('is_active');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('districts');
    }
}
