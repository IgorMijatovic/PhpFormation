<?php
namespace App\Blog\Table;

class PostTable
{
    /**
     * @var \PDO
     */
    private $pdo;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**Pagine les articles
     * @return \stdClass[]
     */
    public function findPaginated(): array
    {

        return $this->pdo
                    ->query('SELECT * FROM posts ORDER BY created_at DESC LIMIT 10')
                    ->fetchAll();
    }

    /**
     * Recupere un article a partir d un id
     * @param int $id
     * @return \stdClass
     */
    public function find(int $id): \stdClass
    {
        $query = $this->pdo
                    ->prepare(
                        'SELECT * FROM posts where id=?'
                    );
        $query->execute([$id]);

        return $query->fetch();
    }
}
