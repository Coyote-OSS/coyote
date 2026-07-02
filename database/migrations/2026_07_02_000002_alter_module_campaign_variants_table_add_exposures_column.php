<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('exposures')->default(0);
        });
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->unsignedBigInteger('exposures')->change();
        });
    }

    public function down(): void {
        Schema::table('module_campaign_variants', function (Blueprint $table) {
            $table->dropColumn('exposures');
        });
    }
};
