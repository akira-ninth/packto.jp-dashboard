<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            // 'basic' | 'pro' (将来追加可能)
            $table->string('slug')->unique();
            $table->string('name');
            // 機能フラグ: { "image": true, "text": false } など
            // Cloudflare worker 側の PLAN_FEATURES と同期する
            $table->json('features');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
