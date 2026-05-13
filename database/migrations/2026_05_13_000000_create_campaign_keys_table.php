<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('campaign_keys', function (Blueprint $table) {
            $table->string('key')->primary();
        });
    }

    public function down(): void {
        Schema::dropIfExists('campaign_keys');
    }
};
