<?php
namespace Framework\Database;

use Framework\Database\PaginatedQuery;
use Pagerfanta\Pagerfanta;

class Table
{
    /**
     * @var null|\PDO
     */
    protected $pdo;

    /**
     * @var string
     */
    protected $table;

    /**
     * Entity a utiliser
     * @var string
     */
    protected $entity = \stdClass::class;

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return Query
     */
    public function makeQuery(): Query
    {
        return (new Query($this->pdo))->from($this->table, $this->table[0])->into($this->entity);
    }
    /**
     * recupere un element a parti de son id
     * @param int $id
     * @return mixed
     * @throws NoRecordException
     */
    public function find(int $id)
    {
        return $this->makeQuery()->where("id = $id")->fetchOrFail();
    }

    /**
     * Recupere une liste clef valeurs de nos
     * enregistrements
     */
    public function findList(): array
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
     * @return Query
     */
    public function findAll(): Query
    {
        return $this->makeQuery();
    }

    /**
     * recupere une ligne par rapport a un champs
     *
     * @param string $field
     * @param string $value
     * @return mixed
     */
    public function findBy(string $field, string $value)
    {
        return $this->makeQuery()->where("$field = :field")->params(["field" => $value])->fetchOrFail();
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
    public function delete(int $id): bool
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
    public function exists($id): bool
    {
        $statement = $this->pdo->prepare("SELECT id FROM {$this->table} WHERE id = ?");
        $statement->execute([$id]);

        return $statement->fetchColumn() !== false;
    }

    /**
     * Permet d executer une requete et de
     * recuperer le premier resultat
     *
     * @param string $query
     * @param array $params
     * @return mixed
     * @throws NoRecordException
     */
    protected function fetchOrFail(string $query, array $params = [])
    {
        $query = $this->pdo->prepare($query);
        $query->execute($params);
        if ($this->entity) {
            $query->setFetchMode(\PDO::FETCH_CLASS, $this->entity);
        }
        $record = $query->fetch();
        if ($record === false) {
            throw new NoRecordException();
        }

        return $record;
    }

    /**
     * recupere le nombre d enregistrements
     * @return int
     */
    public function count(): int
    {
        return $this->makeQuery()->count();
    }
}
