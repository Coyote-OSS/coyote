<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('temp_emails', function (Blueprint $table) {
            $table->id();
            $table->text('domain');
            $table->text('category');
        });
    }

    public function down(): void {
        Schema::dropIfExists('temp_emails');
    }
};
