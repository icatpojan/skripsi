<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDistrictIdToWasteReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('waste_reports', function (Blueprint $table) {
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('set null');
            $table->index('district_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('waste_reports', function (Blueprint $table) {
            $table->dropForeign(['district_id']);
            $table->dropIndex(['district_id']);
            $table->dropColumn('district_id');
        });
    }
}
