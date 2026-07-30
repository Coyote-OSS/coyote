<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->boolean('enabled')->default(true);
        });
    }

    public function down(): void {
        Schema::dropColumns('module_campaign_variants', ['enabled']);
    }
};
