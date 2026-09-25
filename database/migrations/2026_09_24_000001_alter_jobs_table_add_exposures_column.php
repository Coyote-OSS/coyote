<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('jobs', function (Blueprint $table) {
            $table->unsignedInteger('exposures')->nullable();
        });
    }

    public function down(): void {
        Schema::dropColumns('jobs', ['exposures']);
    }
};
