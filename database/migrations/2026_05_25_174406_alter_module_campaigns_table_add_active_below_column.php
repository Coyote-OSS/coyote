<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaigns', function (Blueprint $table) {
            $table->integer('target_views')->nullable();
        });
    }

    public function down(): void {
        Schema::dropColumns('module_campaigns', ['target_views']);
    }
};
