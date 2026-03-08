<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('server_id')->constrained()->cascadeOnDelete();
            $table->foreignId('git_provider_id')->nullable()->constrained()->nullOnDelete();
            $table->string('domain');
            $table->string('project_type')->default('laravel');
            $table->string('directory')->nullable();
            $table->string('web_directory')->default('/public');
            $table->string('php_version')->default('8.3');
            $table->string('repository')->nullable();
            $table->string('branch')->default('main');
            $table->text('deploy_script')->nullable();
            $table->string('webhook_token')->nullable()->unique();
            $table->string('visibility')->default('private');
            $table->boolean('ssl_enabled')->default(false);
            $table->timestamp('ssl_expires_at')->nullable();
            $table->string('status')->default('installing');
            $table->timestamps();

            $table->index(['server_id', 'domain']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sites');
    }
};
