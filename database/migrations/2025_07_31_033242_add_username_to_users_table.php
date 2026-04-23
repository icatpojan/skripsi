<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUsernameToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->after('name'); // Username unik
            $table->string('phone')->nullable()->after('email'); // Nomor telepon
            $table->text('address')->nullable()->after('phone'); // Alamat user
            $table->enum('role', ['user', 'admin'])->default('user')->after('address'); // Role user
            $table->boolean('is_active')->default(true)->after('role'); // Status aktif user
            $table->timestamp('last_login_at')->nullable()->after('is_active'); // Waktu login terakhir

            // Index untuk optimasi
            $table->index(['username', 'is_active']);
            $table->index('role');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['username', 'is_active']);
            $table->dropIndex(['role']);
            $table->dropColumn(['username', 'phone', 'address', 'role', 'is_active', 'last_login_at']);
        });
    }
}
