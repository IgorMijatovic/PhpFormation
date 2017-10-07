<?php
namespace Framework\Session;

class PHPSession implements SessionInterface
{
    /**
     * Assure que la session est demaree
     */
    private function esureStarted()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Recupere une information de la session
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        $this->esureStarted();
        if (array_key_exists($key, $_SESSION)) {
            return $_SESSION[$key];
        }

        return $default;
    }

    /**
     * Ajoute une information en session
     *
     * @param string $key
     * @param $value
     */
    public function set(string $key, $value): void
    {
        $this->esureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * supprime une information en session
     *
     * @param string $key
     */
    public function delete(string $key): void
    {
        $this->esureStarted();
        unset($_SESSION[$key]);
    }
}
