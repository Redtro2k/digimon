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
        Schema::table('users', function (Blueprint $table) {
            //
            $table->enum('gender', ['male', 'female'])->default('male')->after('email');
            $table->text('profile')->nullable();
            $table->dateTime('logged_dt')->nullable();
            $table->dateTime('last_login_dt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('logged_dt', 'last_login_dt', 'profile', 'gender');
        });
    }
};
