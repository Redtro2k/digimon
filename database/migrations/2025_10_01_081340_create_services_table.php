<?php

use App\Models\User;
use App\Models\Vehicle;
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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vehicle::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'assigned_mras_id')->nullable()->constrained()->nullOnDelete();
            $table->string('last_service_availed')->nullable();
            $table->string('recommended_pm_service')->nullable();
            $table->string('forecast_status')->nullable();
            $table->date('forecast_date');
            $table->string('personal_email')->nullable();
            $table->string('personal_mobile')->nullable();
            $table->string('company_email_address')->nullable();
            $table->string('company_mobile')->nullable();
            $table->boolean('has_fpm')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
