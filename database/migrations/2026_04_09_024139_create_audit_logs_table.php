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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // 操作した人 (削除されても残せるよう nullable + onDelete set null)
            $table->foreignId('actor_user_id')->nullable()->index();
            $table->string('actor_email')->nullable(); // ユーザ削除後も追跡できるよう保存
            $table->string('actor_role')->nullable();
            // 操作内容
            // 例: 'customer.create', 'customer.update', 'customer.delete',
            //     'customer_user.create', 'customer_user.delete',
            //     'master.create', 'master.delete',
            //     'auth.password_change', 'auth.login', 'auth.logout'
            $table->string('action', 64)->index();
            // 操作対象 (any model)
            $table->string('target_type', 64)->nullable(); // 'customer', 'user', 'plan'
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('target_label')->nullable(); // 'rays-hd' / 'admin@newco.com' 等の人間用ラベル
            // 詳細メタ (変更前後の値、IP、UA など)
            $table->json('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
