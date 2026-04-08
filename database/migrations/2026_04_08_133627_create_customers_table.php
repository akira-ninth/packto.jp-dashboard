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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            // packto.jp の左側のサブドメイン (例: 'rays-hd' → rays-hd.packto.jp)
            $table->string('subdomain')->unique();
            // 表示用顧客名
            $table->string('display_name');
            // 配信元 origin URL (例: 'https://rays-hd.com')
            $table->string('origin_url');
            // 加入プラン
            $table->foreignId('plan_id')->constrained()->restrictOnDelete();
            // 有効/停止フラグ
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
