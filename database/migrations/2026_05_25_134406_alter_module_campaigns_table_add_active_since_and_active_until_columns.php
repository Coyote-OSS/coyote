<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('module_campaigns', function (Blueprint $table) {
            $table->timestamp('active_since')->nullable();
            $table->timestamp('active_until')->nullable();
        });
    }

    public function down(): void {
        Schema::dropColumns('module_campaigns', ['active_since', 'active_until']);
    }
};
