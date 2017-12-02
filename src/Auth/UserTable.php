<?php
namespace App\Auth;

use Framework\Database\Table;

class UserTable extends Table
{
    protected $table = "users";

    public function __construct(\PDO $pdo, $entity = User::class)
    {
        parent::__construct($pdo);
        $this->entity = $entity;
    }
}
