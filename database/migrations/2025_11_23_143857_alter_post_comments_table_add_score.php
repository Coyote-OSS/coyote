<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('post_comments', function (Blueprint $table) {
            $table->integer('score')->default(0);
        });
    }

    public function down(): void {
        Schema::dropColumns('post_comments', 'score');
    }
};
