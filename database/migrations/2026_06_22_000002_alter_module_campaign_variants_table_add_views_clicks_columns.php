<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('views')->default(0);
            $table->unsignedBigInteger('clicks')->default(0);
        });
    }

    public function down(): void {
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->dropColumn('views');
            $table->dropColumn('clicks');
        });
    }
};
