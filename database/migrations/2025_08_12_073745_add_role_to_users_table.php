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
        // usersテーブルにroleカラムを追加
        // roleカラムは文字列型で、デフォルト値は'user'と
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->index(); // 'user' / 'admin' など
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // usersテーブルからroleカラムを削除
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->index(); // 'user' / 'admin' など
        });
    }
};
