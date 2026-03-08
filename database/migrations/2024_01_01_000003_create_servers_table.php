<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cloud_credential_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('provider')->default('digitalocean');
            $table->string('provider_server_id')->nullable();
            $table->string('region');
            $table->string('size');
            $table->string('ip_address')->nullable();
            $table->string('private_ip_address')->nullable();
            $table->string('os')->default('ubuntu-22-04-x64');
            $table->string('php_version')->default('8.3');
            $table->string('database_type')->default('postgresql');
            $table->string('status')->default('provisioning');
            $table->integer('ssh_port')->default(22);
            $table->text('sudo_password')->nullable();
            $table->text('provision_command')->nullable();
            $table->boolean('claude_code_installed')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
