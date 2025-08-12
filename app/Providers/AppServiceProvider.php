<?php

namespace App\Providers;
use Illuminate\Support\ServiceProvider;

// LaravelのGateを使用して、ユーザーの権限を管理したい場合
use App\Models\User;
use Illuminate\Support\Facades\Gate;

// Postモデルに対するポリシーを登録したい場合
use App\Models\Post;
use App\Policies\PostPolicy;

/**
 * This service provider is used to register application services.
 * It is automatically loaded by the framework.
 * 
 * アプリケーションサービスを登録するためのサービスプロバイダです。
 * フレームワークによって自動的にロードされます。
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     * 
     * ここにGateやPolicyの登録などを追加できる
     * 
     * Laravel 11/12 でも Policy を手動登録したい場合は AppServiceProvider::boot() に書いて問題ない。
     * ただし、11/12 では標準の命名／配置を守れば 自動検出（auto-discovery） されるので、多くの場合は記述不要。
     */
    public function boot(): void
    {
        //ここにGate::policy()の登録など、アプリケーションのブートストラップ処理を記述できる。
        // 例: Gate::policy(User::class, UserPolicy::class);
        // ただし、Breezeでは特に必要な処理はないため、
        // このメソッドは空のままにしておくことが一般的

        // 例: 管理画面アクセス権
        // Userテーブルのroleカラムが 'admin' のユーザーのみが管理画面にアクセスできるようにする
        Gate::define('access-admin', function (User $user) {
            return $user->role === 'admin';
        });

        // Post モデルに対するポリシーを登録　（Postモデルは PostPolicy を使う）
        // Gate::policy(Post::class, PostPolicy::class);   
    }
}
