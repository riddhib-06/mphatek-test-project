<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fd_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('min_days');
            $table->unsignedInteger('max_days');
            $table->decimal('general_rate', 5, 2);
            $table->decimal('senior_rate', 5, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fd_rates');
    }
};
