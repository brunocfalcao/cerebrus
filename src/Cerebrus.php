<?php

namespace Brunocfalcao\Cerebrus;

class Cerebrus
{
    public const PHP_SESSION_DISABLED = 'SESSION_DISABLED';

    public const PHP_SESSION_NONE = 'SESSION_NONE';

    public const PHP_SESSION_ACTIVE = 'SESSION_ACTIVE';

    public function __construct(string $path = null)
    {
        $status = session_status();

        switch ($status) {
            case PHP_SESSION_DISABLED:
                throw new \Exception('Your web app cannot use sessions. Cerebrus aborted');

            case PHP_SESSION_NONE:
                $this->start($path);
                break;
        }
    }

    /**
     * Check if a session variable exists.
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $this->checkDuration($key);
        return isset($_SESSION[$key]);
    }

    /**
     * Get a session variable.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $this->checkDuration($key);
        return $_SESSION[$key] ?? null;
    }

    /**
     * Get all session variables.
     *
     * @return array|null
     */
    public function all(): ?array
    {
        return $_SESSION;
    }

    /**
     * Set a session variable with a specific duration.
     *
     * @param string $key
     * @param mixed $value
     * @param int $seconds
     * @return void
     */
    public function set(string $key, mixed $value, int $seconds = null): void
    {
        $_SESSION[$key] = $value;

        if ($seconds !== null) {
            $expirationTime = time() + $seconds;
            $_SESSION["{$key}__duration"] = $expirationTime;
        }
    }

    /**
     * Unset a session variable.
     *
     * @param string $key
     * @return void
     */
    public function unset(string $key): void
    {
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    /**
     * Destroy the session.
     *
     * @return void
     */
    public function destroy(): void
    {
        session_destroy();
    }

    /**
     * Get the session status.
     *
     * @return int
     */
    public function getStatus(): int
    {
        return session_status();
    }

    /**
     * Get the session ID.
     *
     * @return string
     */
    public function getId(): string
    {
        return session_id();
    }

    /**
     * Start the session.
     *
     * @param string|null $path
     * @return void
     */
    private function start(string|null $path = 'tmp'): void
    {
        $sessionPath = $path ?? base_path('tmp');

        if (!is_dir($sessionPath)) {
            mkdir($sessionPath);
        }

        session_save_path($sessionPath);

        session_start();
    }

    /**
     * Check the duration of a session variable and unset it if expired.
     *
     * @param string $key
     * @return void
     */
    private function checkDuration(string $key): void
    {
        if (isset($_SESSION["{$key}__duration"]) && time() > $_SESSION["{$key}__duration"]) {
            unset($_SESSION[$key]);
            unset($_SESSION["{$key}__duration"]);
        }
    }
}
