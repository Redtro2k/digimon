<?php

use App\Models\Dealer;
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
        Schema::table('services', function (Blueprint $table) {
            $table->dateTime('assigned_date')->nullable()->after('assigned_mras_id');
            $table->foreignIdFor(Dealer::class, 'dealer_id')->nullable()->after('assigned_date')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('assigned_date');
            $table->dropForeignIdFor(Dealer::class, 'dealer_id');
        });
    }
};
