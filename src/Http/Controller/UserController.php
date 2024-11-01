<?php

namespace WaHelp\Newsletter\Http\Controller;

use WaHelp\Core\Http\JsonResponse;
use WaHelp\Core\Http\Request;
use WaHelp\Newsletter\Service\UserFileImporter;
use WaHelp\Newsletter\Service\UserPaginator;

class UserController
{
    public function import(Request $request, UserFileImporter $userImporter): JsonResponse
    {
        $importedCount = $userImporter->importByRequest($request);

        return new JsonResponse(['count' => $importedCount]);
    }

    public function getList(Request $request, UserPaginator $userPaginator): JsonResponse
    {
        $perPage = $request->get('per_page', 100);
        $paginator = $userPaginator->getList(
            $request,
            $perPage,
            $_SERVER['HTTP_HOST'],
            [],[], ['id', 'desc']
        );

        return new JsonResponse($paginator->toArray());
    }
}