<?php

use App\Models\Category;
use App\Models\Service;
use App\Models\User;
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
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class, 'assigned_to')->constrained()->cascadeOnDelete();
            $table->integer('attempt')->default(1);
            $table->enum('sub_result', ['successful', 'unsuccessful'])->default('successful');
            $table->foreignIdFor(Category::class)->constrained()->cascadeOnDelete();
            $table->dateTime('call_back')->nullable();
            $table->foreignIdFor(Service::class)->constrained()->cascadeOnDelete();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('duration')->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reminders');
    }
};
