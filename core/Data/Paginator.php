<?php

namespace WaHelp\Core\Data;

use WaHelp\Core\Http\Request;
use WaHelp\Newsletter\Helper\RequestHelper;

class Paginator
{
    protected Request $request;
    protected int $totalItems;
    protected int $lastPage;
    protected int $currentPage;
    protected array $data = [];
    protected ?string $firstPageUrl = null;
    protected ?string $lastPageUrl = null;
    protected ?string $nextPageUrl = null;
    public static string $queryPageParam = 'page';
    protected int $perPage;
    protected string $path;

    public function __construct(Request $request, array $records, string $baseUrl, int $totalItems, int $perPage)
    {
        $uri = $request->getUri();
        $uriWithoutPageQuery = RequestHelper::removeQueryParam($uri, self::$queryPageParam);

        $currentPage = (int)$request->get(self::$queryPageParam, 1);
        $perPage = (int)$request->get('per_page', $perPage);
        $lastPage = (int)ceil($totalItems / $perPage);
        $nextPage = min($currentPage + 1, $lastPage);
        $pageUri = $uriWithoutPageQuery.'&'.self::$queryPageParam;

        $this->path = $baseUrl;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->data = $records;
        $this->lastPage = $lastPage;
        $this->totalItems = $totalItems;
        $this->firstPageUrl = $pageUri.'=1';
        $this->lastPageUrl = $pageUri.'='.$lastPage;
        $this->nextPageUrl = $pageUri.'='.$nextPage;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'path' => $this->path,
            'data' => $this->data,
            'last_page' => $this->lastPage,
            'total_items' => $this->totalItems,
            'current_page' => $this->currentPage,
            'per_page' => $this->perPage,
            'last_page_url' => $this->lastPageUrl,
            'first_page_url' => $this->firstPageUrl,
            'next_page_url' => $this->nextPageUrl,
        ];
    }
}