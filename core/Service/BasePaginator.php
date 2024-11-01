<?php

namespace WaHelp\Core\Service;

use PDO;
use PDOStatement;
use WaHelp\Core\Data\Paginator;
use WaHelp\Core\Entity\BaseRepository;
use WaHelp\Core\Http\Request;

class BasePaginator
{
    public function __construct(protected BaseRepository $entityRepository)
    {
    }

    public function getList(
        Request $request,
        int $perPage,
        string $baseUrl,
        array $columns = [],
        array $wheres = [],
        array $order = []
    ): Paginator {
        // SQL for getting total count records
        $countSql = $this->entityRepository->getSql(['COUNT(*)'], $wheres);
        $total_items = $this->entityRepository->execute($countSql, $wheres)->fetchColumn();

        // SQL for getting records
        $sql = $this->entityRepository->getSql($columns, $wheres, $order);
        $sql .= ' LIMIT :limit OFFSET :offset';

        // Getting records
        $current_page = $request->get(Paginator::$queryPageParam, 1);
        $skip_items = ($current_page - 1) * $perPage;

        $sth = $this->entityRepository->execute(
            $sql,
            $wheres,
            function (PDOStatement $sth) use ($perPage, $skip_items) {
                $sth->bindParam(':limit', $perPage, PDO::PARAM_INT);
                $sth->bindParam(':offset', $skip_items, PDO::PARAM_INT);
            }
        );

        $records = $sth->fetchAll(PDO::FETCH_ASSOC);

        return new Paginator($request, $records, $baseUrl, $total_items, $perPage);
    }
}