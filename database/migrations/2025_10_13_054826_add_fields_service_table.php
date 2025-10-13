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
        //
        Schema::table('services', function (Blueprint $table) {
            $table->string('personal_provider_number')->nullable();
            $table->string('company_provider_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('personal_provider_number');
            $table->dropColumn('company_provider_number');
        });
    }
};
