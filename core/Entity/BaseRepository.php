<?php

namespace WaHelp\Core\Entity;

use InvalidArgumentException;
use PDO;
use PDOStatement;
use Throwable;
use WaHelp\Core\Database;
use WaHelp\Core\Exception\Database\DatabaseException;

abstract class BaseRepository
{
    abstract public function getTableName(): string;

    public function __construct(protected Database $database)
    {
    }

    public function getConnection(): PDO
    {
        return $this->database->getConnection();
    }

    public function findById(string $table, int $id): ?array
    {
        $sql = "SELECT * FROM $table WHERE id = :id LIMIT 1";

        $stmt = $this->getConnection()->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function getSql(array $columns = [], array $wheres = [], array $order = []): string
    {
        $table = $this->getTableName();
        $columns_string = '*';
        if (count($columns)) {
            $columns_string = implode(',', $columns);
        }

        $sql = "SELECT $columns_string FROM $table";

        if (count($wheres)) {
            $i = 0;
            foreach ($wheres as $where_name => $where) {
                $column = $where['column'];
                $operator = $where['operator'];
                $or_column = $where['or_column'] ?? null;
                if (!$i) {
                    $sql .= " WHERE $column $operator :$where_name";
                } else {
                    $sql .= " AND $column $operator :$where_name";
                }

                if ($or_column) {
                    $sql .= " OR $or_column $operator :$where_name";
                }

                ++$i;
            }
        }

        if (count($order)) {
            $sql .= " ORDER BY $order[0] $order[1]";
        }

        return $sql;
    }

    /**
     * @param  string  $sql
     * @param  array  $wheres
     * @param  callable|null  $callback
     * @return bool|PDOStatement
     */
    public function execute(string $sql, array $wheres = [], callable $callback = null): bool|PDOStatement
    {
        $sth = $this->database->getConnection()->prepare($sql);

        if (count($wheres)) {
            foreach ($wheres as $where_name => $where) {
                $where_value = $where['value'];

                if ($where['operator'] === 'ILIKE' || $where['operator'] === 'LIKE') {
                    $where_value = '%'.$where_value.'%';
                }

                $sth->bindValue($where_name, $where_value, $where['type']);
            }
        }

        if ($callback) {
            $callback($sth);
        }

        $sth->execute();

        return $sth;
    }

    public function record(callable $callback)
    {
        $db = $this->getConnection();
        $table = $this->getTableName();

        return $callback($db, $table);
    }

    public function insertOne(array $data): int
    {
        try {
            $db = $this->getConnection();
            $columns = array_keys($data);
            $columnsString = implode(', ', array_keys($data));
            $bindingsString = implode(', ', array_map(static function ($column) {
                return ':'.$column;
            }, $columns));

            $table = $this->getTableName();
            $sql = "INSERT INTO $table ($columnsString) VALUES ($bindingsString)";
            $query = $db->prepare($sql);

            foreach ($data as $key => $value) {
                if (is_array($value) && isset($value['value'], $value['type'])) {
                    $query->bindValue(":$key", $value['value'], $value['type']);
                } else {
                    $query->bindValue(":$key", $value, PDO::PARAM_STR);
                }
            }

            $query->execute();

            return (int)$db->lastInsertId();
        } catch (Throwable $e) {
            throw new DatabaseException("Insert ended with error: {$e->getMessage()}.", $e);
        }
    }

    public function insertBatch(array $items, array $columns, array $types = []): bool
    {
        if (empty($items) || empty($columns)) {
            throw new InvalidArgumentException("Items and columns arrays cannot be empty.");
        }

        $table = $this->getTableName();
        $connection = $this->getConnection();
        $columnsString = implode(', ', $columns);

        $values = [];
        $params = [];

        // Заполняем плейсхолдеры
        foreach ($items as $index => $item) {
            $placeholders = [];
            foreach ($columns as $column) {
                if (!array_key_exists($column, $item)) {
                    throw new InvalidArgumentException("Column $column does not exist in data.");
                }

                $placeholder = ":{$column}_{$index}";
                $placeholders[] = $placeholder;

                $params[$placeholder] = [
                    'value' => $item[$column],
                    'type' => $types[$column] ?? PDO::PARAM_STR,
                ];
            }
            $values[] = '('.implode(', ', $placeholders).')';
        }

        $query = "INSERT INTO $table ($columnsString) VALUES ".implode(', ', $values);

        try {
            $stmt = $connection->prepare($query);

            foreach ($params as $placeholder => $param) {
                $stmt->bindValue($placeholder, $param['value'], $param['type']);
            }

            return $stmt->execute();
        } catch (Throwable $e) {
            throw new DatabaseException("Insert ended with error: {$e->getMessage()}.", $e);
        }
    }
}