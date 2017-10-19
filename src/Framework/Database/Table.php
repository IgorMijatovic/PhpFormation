<?php
namespace Framework\Database;

use Framework\Database\PaginatedQuery;
use Pagerfanta\Pagerfanta;

class Table
{
    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var string
     */
    protected $table;

    /**
     * Entity a utiliser
     * @var string|null
     */
    protected $entity;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * PAgine les elements
     *
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public function findPaginated(int $perPage, int $currentPage): Pagerfanta
    {

        $query = new PaginatedQuery(
            $this->pdo,
            $this->paginationQuery(),
            "SELECT count(id) FROM {$this->table}",
            $this->entity
        );

        return (new Pagerfanta($query))
            ->setMaxPerPage($perPage)
            ->setCurrentPage($currentPage);
    }

    protected function paginationQuery()
    {

        return 'SELECT * FROM ' . $this->table;
    }

    /**
     * Recupere un elemet a patir de son id
     * @param int $id
     * @return mixed
     */
    public function find(int $id)
    {
        $query = $this->pdo
            ->prepare(
                'SELECT * FROM ' . $this->table . ' where id=?'
            );
        $query->execute([$id]);
        if ($this->entity) {
            $query->setFetchMode(\PDO::FETCH_CLASS, $this->entity);
        }

        return $query->fetch() ?: null;
    }

    /**
     * Recupere une liste clef valeurs de nos
     * enregistrements
     */
    public function findList():array
    {
        $results = $this->pdo
                        ->query("SELECT id, name FROM {$this->table}")
                        ->fetchAll(\PDO::FETCH_NUM);
        $list = [];
        foreach ($results as $result) {
            $list[$result[0]] = $result[1];
        }

        return $list;
    }

    /**
     * pemet de mettre enregistrement a jour
     * @param int $id
     * @param array $params
     * @return bool
     */
    public function update(int $id, array $params): bool
    {
        $fieldQuery = $this->buildFieldQuery($params);
        $params['id'] = $id;
        $statement = $this->pdo->prepare("UPDATE " . $this->table . " SET $fieldQuery WHERE id = :id");

        return $statement->execute($params);
    }

    /**
     * Inserer un element
     * @param array $params
     * @return bool
     */
    public function insert(array $params): bool
    {
        $fields = array_keys($params);
        $values = array_map(function ($field) {
            return ':' . $field;
        }, $fields);
        $statement = $this->pdo->prepare(
            "INSERT INTO {$this->table} (" .
            join(',', $fields) .
            ") VALUES (" .
            join(',', $values) .
            ")"
        );
        return $statement->execute($params);
    }

    /**
     * Supprime article de la base de donnes
     * @param int $id
     * @return bool
     */
    public function delete(int $id):bool
    {
        $statement = $this->pdo->prepare('DELETE FROM ' . $this->table . ' where id = ?');

        return $statement->execute([$id]);
    }

    private function buildFieldQuery(array $params)
    {

        return join(', ', array_map(function ($field) {
            return "$field = :$field";
        }, array_keys($params)));
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @return string
     */
    public function getEntity(): string
    {
        return $this->entity;
    }

    /**
     * @return \PDO
     */
    public function getPdo(): \PDO
    {
        return $this->pdo;
    }

    /**
     * verifier qu un enregristrement exist
     * @param $id
     * @return bool
     */
    public function exists($id):bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id = ?");
        $statement->execute([$id]);

        return $statement->fetchColumn() !== false;
    }
}
