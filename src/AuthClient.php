<?php

namespace Streeboga\Genesis;

class AuthClient
{
    public function __construct(private GenesisClient $client)
    {
    }

    public function login(array $credentials): array
    {
        return $this->client->post('auth/login', $credentials);
    }

    public function register(array $data): array
    {
        return $this->client->post('auth/register', $data);
    }

    public function refresh(string $refreshToken): array
    {
        return $this->client->post('auth/refresh', ['refresh_token' => $refreshToken]);
    }

    public function logout(): array
    {
        return $this->client->post('auth/logout', []);
    }

    public function forgotPassword(array $data): array
    {
        return $this->client->post('auth/password/forgot', $data);
    }

    public function resetPassword(array $data): array
    {
        return $this->client->post('auth/password/reset', $data);
    }

    public function resendVerification(array $data): array
    {
        return $this->client->post('auth/resend-verification', $data);
    }

    public function verifyEmail(array $data): array
    {
        return $this->client->post('auth/email/verify', $data);
    }

    public function twoFaChallenge(array $data): array
    {
        return $this->client->post('auth/2fa/challenge', $data);
    }

    public function twoFaVerify(array $data): array
    {
        return $this->client->post('auth/2fa/verify', $data);
    }

    public function sendOtp(array $data): array
    {
        return $this->client->post('auth/otp/send', $data);
    }

    public function verifyOtp(array $data): array
    {
        return $this->client->post('auth/otp/verify', $data);
    }

    public function getSession(string $sessionToken): array
    {
        return $this->client->get("auth/sessions/{$sessionToken}");
    }

    /**
     * Авторизовать пользователя по email и создать токен сессии
     *
     * @param array $data Данные для авторизации
     * @return array
     */
    public function authenticateByEmail(array $data): array
    {
        return $this->client->post('auth/authenticate-by-email', $data);
    }

    /**
     * Проверить валидность токена сессии
     *
     * @param string $sessionToken Токен сессии
     * @return array
     */
    public function validateSession(string $sessionToken): array
    {
        return $this->client->post('auth/validate-session', [
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Получить URL для оплаты с токеном сессии
     *
     * @param string $sessionToken Токен сессии
     * @param string $planUuid UUID плана
     * @return array
     */
    public function getPaymentUrl(string $sessionToken, string $planUuid): array
    {
        return $this->client->post('auth/get-payment-url', [
            'session_token' => $sessionToken,
            'plan_uuid' => $planUuid
        ]);
    }

    /**
     * Продлить сессию
     *
     * @param string $sessionToken Токен сессии
     * @param int $hours Количество часов для продления
     * @return array
     */
    public function extendSession(string $sessionToken, int $hours = 2): array
    {
        return $this->client->post('auth/extend-session', [
            'session_token' => $sessionToken,
            'hours' => $hours
        ]);
    }

    /**
     * Завершить сессию (logout)
     *
     * @param string $sessionToken Токен сессии
     * @return array
     */
    public function destroySession(string $sessionToken): array
    {
        return $this->client->post('auth/destroy-session', [
            'session_token' => $sessionToken
        ]);
    }

    /**
     * Получить информацию о текущей сессии
     *
     * @param string $sessionToken Токен сессии
     * @return array
     */
    public function getSessionInfo(string $sessionToken): array
    {
        return $this->client->post('auth/session-info', [
            'session_token' => $sessionToken
        ]);
    }
}
