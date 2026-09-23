<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedInteger('clicks')->nullable();
        });
    }

    public function down(): void {
        Schema::dropColumns('jobs', ['clicks']);
    }
};
