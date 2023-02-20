<?php

namespace Brunocfalcao\Cerebrus;

class Cerebrus
{
    private static $_instance = null;

    public const PHP_SESSION_DISABLED = 'SESSION_DISABLED';

    public const PHP_SESSION_NONE = 'SESSION_NONE';

    public const PHP_SESSION_ACTIVE = 'SESSION_ACTIVE';

    public function __construct(string $path = null)
    {
        $status = session_status();

        switch ($status) {
            case PHP_SESSION_DISABLED:
                throw new \Exception('Your web app cannot use sessions. Cerebrus aborted');
                break;

            case PHP_SESSION_NONE:
                $this->start($path);
                break;
        }
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function get(string $key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    public function all(): ?array
    {
        return $_SESSION;
    }

    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function unset(string $key): void
    {
        if (array_key_exists($key, $_SESSION)) {
            unset($_SESSION[$key]);
        }
    }

    public function destroy(): void
    {
        session_destroy();
    }

    public function getStatus(): int
    {
        return session_status();
    }

    public function getId(): string
    {
        return session_id();
    }

    private function start(string|null $path = 'tmp'): void
    {
        try {
            session_start();
        } catch (\Exception $e) {
            /**
             * Mostly a file write directory exception.
             * Creates and set the session writable directory path
             * in the /tmp on your Laravel project base path.
             */
            if (! is_dir(session_save_path())) {
                $path = $path ?? base_path('tmp');
                @mkdir($path);
                session_save_path($path);
            }

            session_start();
        }
    }
}
