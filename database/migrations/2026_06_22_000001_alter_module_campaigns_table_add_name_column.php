<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaigns', function (Blueprint $table) {
            $table->string('name')->nullable();
            $table->dropUnique(['campaign_key']);
        });
    }

    public function down(): void {
        Schema::table('module_campaigns', function (Blueprint $table) {
            $table->unique('campaign_key');
        });
        Schema::dropColumns('module_campaigns', ['name']);
    }
};
