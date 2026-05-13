<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('module_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('campaign_key')->unique();
            $table->string('sidebar');
            $table->string('horizontal');
            $table->string('redirect_url');
        });
    }

    public function down(): void {
        Schema::dropIfExists('module_campaigns');
    }
};
