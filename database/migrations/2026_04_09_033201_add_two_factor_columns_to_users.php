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
        Schema::table('users', function (Blueprint $table) {
            // Phase 13n: master 用 TOTP 2FA
            // secret は base32 文字列、平文保存だと漏洩時に直接 TOTP 生成されるので暗号化保存
            $table->text('two_factor_secret')->nullable()->after('password');
            // recovery_codes は 8 桁 6 個 (json 配列、暗号化)
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            // 設定完了 (確認 OK) のタイムスタンプ
            $table->timestamp('two_factor_confirmed_at')->nullable()->after('two_factor_recovery_codes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at']);
        });
    }
};
