<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * ログイン時のバリデーションルール
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        // ログイン試行回数が一定数を超えている場合、Lockoutイベントを発行し、エラーをスローする
        $this->ensureIsNotRateLimited();

        // Auth::attemptを使用して、メールアドレスとパスワードの組み合わせをチェック
        // 失敗した場合、試行回数を増加させ、エラーメッセージを返す
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 認証成功時には、試行回数をリセット
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * ログイン要求がレート制限に抵触していないことを確認する
     *
     * 一定回数（ここでは5回）以上、短時間に失敗した場合は
     * Lockoutイベントを発火し、待機秒数を含むバリデーション例外を投げる
     * 
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        // throttleKey() をキーとして、直近の失敗回数が 5 回未満なら通す
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // 失敗回数超過：アカウントロック相当のイベントを発火（監査/通知用）
        event(new Lockout($this));

        // 次に試行できるまでの残り秒数を取得
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // 翻訳メッセージに秒数/分数を埋め込んでエラーにする
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     * 
     * レート制限で用いるスロットルキーを生成する.
     *
     * ログインID（email）を小文字化＋正規化したものと、
     * クライアントIPを連結して一意キーを作る。
     * これにより、同じメールでもIPが異なると別カウントになる
     */
    
    public function throttleKey(): string
    {
        // 例: "user@example.com|203.0.113.1" のようなキーを作成
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
