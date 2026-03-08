<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('queue_workers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained()->cascadeOnDelete();
            $table->string('connection')->default('redis');
            $table->string('queue')->default('default');
            $table->integer('timeout')->default(60);
            $table->integer('sleep')->default(3);
            $table->integer('tries')->default(3);
            $table->integer('processes')->default(1);
            $table->string('status')->default('stopped');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('queue_workers');
    }
};
