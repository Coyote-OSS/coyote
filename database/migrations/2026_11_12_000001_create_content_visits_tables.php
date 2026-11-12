<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('content_visits', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->uuid('guest_id')->nullable();
            $table->string('content_type')->nullable();
            $table->timestamp('created_at');
            $table->index('created_at');
        });
        Schema::create('content_visits_monthly', function (Blueprint $table) {
            $table->date('month');
            $table->integer('user_id')->nullable();
            $table->uuid('guest_id')->nullable();
            $table->string('content_type')->nullable();
            $table->unsignedInteger('visits');
            $table->index(['month', 'user_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('content_visits_monthly');
        Schema::dropIfExists('content_visits');
    }
};
