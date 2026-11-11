<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // product, blog, branch, static, calculator
            $table->string('title');
            $table->string('slug');
            $table->string('category')->nullable();
            $table->text('summary')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_published')->default(true);
            $table->boolean('noindex')->default(false);
            $table->string('canonical_url')->nullable();
            $table->string('redirect_url')->nullable();
            $table->unsignedSmallInteger('redirect_code')->default(301);
            $table->timestamps();

            $table->unique(['type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
