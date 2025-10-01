<?php

use App\Models\{Dealer, User};
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
        Schema::create('dealers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('acronym');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('users_dealers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Dealer::class);
            $table->foreignIdFor(User::class);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dealers');
        Schema::dropIfExists('users_dealers');
    }
};
