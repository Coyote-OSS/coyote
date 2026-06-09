<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('module_campaign_variants', function (Blueprint $table) {
            $table->id();
            $table->integer('campaign_id')->index();
            $table->foreign('campaign_id')
                ->references('id')
                ->on('module_campaigns')
                ->onDelete('cascade');
            $table->string('image_url');
            $table->string('type');
        });
    }

    public function down(): void {
        Schema::dropIfExists('module_campaign_variants');
    }
};
