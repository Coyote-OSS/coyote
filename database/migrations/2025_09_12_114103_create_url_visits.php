<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('url_visits', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->timestamp('created_at');
        });
    }

    public function down(): void {
        Schema::dropIfExists('url_visits');
    }
};
