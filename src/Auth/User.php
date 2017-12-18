<?php
namespace App\Auth;

class User implements \Framework\Auth\User
{
    public $id;

    public $username;

    public $email;

    public $password;

    public $passwordReset;

    public $passwordResetAt;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string[]
     */
    public function getRoles(): array
    {
        return [];
    }

    /**
     * @return \DateTime
     */
    public function getPasswordResetAt(): ?\DateTime
    {
        return $this->passwordResetAt;
    }

    /**
     * @param mixed $passwordResetAt
     */
    public function setPasswordResetAt($passwordResetAt): void
    {
        if (is_string($passwordResetAt)) {
            $this->passwordResetAt = new \DateTime($passwordResetAt);
        } else {
            $this->passwordResetAt = $passwordResetAt;
        }
    }

    /**
     * @return mixed
     */
    public function getPasswordReset()
    {
        return $this->passwordReset;
    }

    /**
     * @param mixed $passwordReset
     */
    public function setPasswordReset($passwordReset): void
    {
        $this->passwordReset = $passwordReset;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }
}
