<?php

namespace WaHelp\Newsletter\Service;

use Iterator;
use PDO;
use RuntimeException;
use WaHelp\Core\Database;

class IntervalIterator implements Iterator
{
    protected int $step = 100;
    protected array $params = [];
    protected ?array $current = null;

    public function __construct(
        protected Database $db,
        protected string $tableName,
        protected string $idColumn = 'id',
        protected array $selectColumns = []
    ) {
        $this->count();
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setStep(int $step): static
    {
        $this->step = $step;

        return $this;
    }

    public function current(): ?array
    {
        return $this->current;
    }

    public function next(): void
    {
        $result = false;
        $params = $this->params;
        $step = $this->step;
        $max = (int)$params['max'];
        $min = (int)$params['min'];

        if ($min <= $max) {
            $newMin = ($min + $step > $max) ? $max : $min + $step;
            $result = $this->getChunkInterval($min, $newMin);
        }
        if (!$result) {
            $this->current = [];
        }
    }

    public function key(): false
    {
        return false;
    }

    public function valid(): bool
    {
        return (bool)$this->current();
    }

    public function rewind(): void
    {
        // nothing
    }

    public function hasSelectColumns(): bool
    {
        return isset($this->selectColumns[0]);
    }

    protected function getChunkInterval(int $min, int $max): true
    {
        if ($min > $max) {
            throw new RuntimeException('Min can not be bigger than Max.');
        }

        $selectColumns = $this->hasSelectColumns() ? implode(',', $this->selectColumns) : '*';

        $sql = "SELECT ".$selectColumns." FROM ".$this->tableName." WHERE ".$this->idColumn." BETWEEN ".$min." AND ".$max;

        $result = $this->db->getConnection()->query($sql);

        if ($result === false) {
            throw new RuntimeException('Failed to execute query.');
        }

        $this->current = $result->fetchAll(PDO::FETCH_ASSOC);

        $this->setParam('min', ++$max);

        return true;
    }

    protected function count(): void
    {
        $sql = "SELECT count("
            .$this->idColumn.") as count, max("
            .$this->idColumn.") as max, min("
            .$this->idColumn.") as min FROM ".$this->tableName;

        $resource = $this->db->getConnection()->query($sql);

        if ($resource === false) {
            throw new RuntimeException('Failed to execute query.');
        }

        $row = $resource->fetch();

        if (!isset($row['max'], $row['min'])) {
            throw new RuntimeException('Failed to retrieve max or min values.');
        }

        $delta = $row['max'] - $row['min'];
        $iterations_count = ceil(++$delta / $this->step);

        $params = $row;
        $params['iterations_count'] = $iterations_count;

        $this->setParams($params);
    }

    protected function setParams(array $params): void
    {
        if (!$this->params) {
            $this->params = $params;
        }
    }

    protected function setParam(string $key, mixed $value): static
    {
        $this->params[$key] = $value;

        return $this;
    }
}