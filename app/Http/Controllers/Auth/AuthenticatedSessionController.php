<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     * LoginRequestはlaravelのFormRequestを拡張したクラス
     * LoginRequestのrulesメソッドで、email、passwordのバリデーションはこのメソッド到達前に実行済み。
     * 認証成功後はsession固定攻撃対策でsessionIDを再発行する。
     * 直前にアクセスしようとしていた保護ページがあればそこへ、無ければ dashboard へ。
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // ログイン試行回数、認証試行、試行回数のリセット
        $request->authenticate();

        // session固定攻撃対策：ログイン直後は必ずsessionIDを再生成
        $request->session()->regenerate();

        // intended(): 認証ミドルウェア(auth)にリダイレクトされた場合、
        // 元々開きたかったページに戻す。無ければ dashboard へ。
        // absolute: false を指定すると相対URLを生成（プロキシ環境などで有用な場合あり）
        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
