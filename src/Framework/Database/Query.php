<?php
namespace Framework\Database;

use Pagerfanta\Pagerfanta;
use Traversable;

/**
 * Class Query qui represene querybuilder
 * iterator pour pouvoir faire foreache
 * array acces pour pouvoir acceder aux elements sous forme de tableau
 * @package Framework\Database
 */
class Query implements \IteratorAggregate
{
    private $select;

    private $from;

    private $where = [];

    private $orderBy = [];

    private $limit;

    private $joins;

    private $pdo;

    private $params = [];

    private $entity;

    public function __construct(?\PDO $pdo = null)
    {
        $this->pdo = $pdo;
    }

    public function from(string $table, ?string $alias = null): self
    {
        if ($alias) {
            $this->from[$table] = $alias;
        } else {
            $this->from[] = $table;
        }


        return $this;
    }

    public function select(string ...$fields): self
    {
        $this->select = $fields;

        return $this;
    }

    /**
     * specifie le limit
     * @param int $length
     * @param int $offset
     * @return Query
     */
    public function limit(int $length, int $offset = 0): self
    {
        $this->limit = "$offset, $length";

        return $this;
    }

    /**
     * specifie  l ordre de recuperation
     * @param string $orderBy
     * @return Query
     */
    public function orderBy(string $orderBy): self
    {
        $this->orderBy[] = $orderBy;
        return $this;
    }

    /**
     * Ajoute une liaison
     * @param string $table
     * @param string $conidtion
     * @param string $type
     * @return Query
     */
    public function join(string $table, string $conidtion, string $type = "left"): self
    {
        $this->joins[$type][] = [$table, $conidtion];

        return $this;
    }

    /**
     * Recupere un resultat
     */
    public function fetch()
    {
        $record = $this->execute()->fetch(\PDO::FETCH_ASSOC);
        if ($record === false) {
            return false;
        }
        if ($this->entity) {
            return Hydrator::hydrate($record, $this->entity);
        }

        return $record;
    }

    /**
     * returne un resultat ou renvoie un exception
     * @return bool|mixed
     * @throws NoRecordException
     */
    public function fetchOrFail()
    {
        $record = $this->fetch();
        if ($record === false) {
            throw new NoRecordException();
        }

        return $record;
    }

    public function fetchAll(): QueryResult
    {
        return new QueryResult(
            $this->execute()->fetchAll(\PDO::FETCH_ASSOC),
            $this->entity
        );
    }

    /**
     * Pagine les resulta
     * @param int $perPage
     * @param int $currentPage
     * @return Pagerfanta
     */
    public function paginate(int $perPage, int $currentPage = 1): Pagerfanta
    {
        $paginator = new PaginatedQuery($this);

        return (new Pagerfanta($paginator))->setMaxPerPage($perPage)->setCurrentPage($currentPage);
    }

    public function where(string ...$condition): self
    {
        $this->where = array_merge($this->where, $condition);
        return $this;
    }

    public function count(): int
    {
        $query = clone $this;
        $table = current($this->from);

        return $query->select("COUNT($table.id)")->execute()->fetchColumn();
    }

    public function params(array $params): self
    {
        $this->params = array_merge($this->params, $params);

        return $this;
    }

    public function __toString()
    {
        $parts = ['SELECT'];
        if ($this->select) {
            $parts[] = join(', ', $this->select);
        } else {
            $parts[] = '*';
        }
        $parts[] = 'FROM';
        $parts[] = $this->buildFrom();
        if (!empty($this->joins)) {
            foreach ($this->joins as $type => $joins) {
                foreach ($joins as [$table, $condition]) {
                    $parts[] = strtoupper($type) . " JOIN $table ON $condition";
                }
            }
        }
        if (!empty($this->where)) {
            $parts[] = 'WHERE';
            $parts[] = "(" . join(') AND (', $this->where) . ")";
        }
        if (!empty($this->orderBy)) {
            $parts[] = 'ORDER BY';
            $parts[] = join(', ', $this->orderBy);
        }
        if ($this->limit) {
            $parts[] = 'LIMIT ' . $this->limit;
        }

        return join(' ', $parts);
    }

    private function buildFrom(): string
    {
        $from = [];
        foreach ($this->from as $key => $value) {
            if (is_string($key)) {
                $from[] = "$key as $value";
            } else {
                $from[] = $value;
            }
        }

        return join(', ', $from);
    }

    private function execute()
    {
        $query = $this->__toString();
        if (!empty($this->params)) {
            $statement = $this->pdo->prepare($query);
            $statement->execute($this->params);

            return $statement;
        }

        return $this->pdo->query($query);
    }

    public function into(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getIterator()
    {
         return $this->fetchAll();
    }
}
