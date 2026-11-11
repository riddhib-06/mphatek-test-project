<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->nullable()->constrained('pages')->nullOnDelete();
            $table->string('product');
            $table->string('name');
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->string('source')->default('direct');
            $table->string('medium')->nullable();
            $table->string('campaign')->nullable();
            $table->string('referrer')->nullable();
            $table->string('landing_page')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
