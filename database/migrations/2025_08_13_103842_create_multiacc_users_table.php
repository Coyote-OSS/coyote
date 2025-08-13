<?php

use Coyote\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('multiacc_users', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->foreignId('multiacc_id');
            $table->foreignId('user_id');
            $table->foreignIdFor(User::class, 'moderator_id');
        });
    }

    public function down(): void {
        Schema::dropIfExists('multiacc_users');
    }
};
