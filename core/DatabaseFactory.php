<?php

namespace WaHelp\Core;

use RuntimeException;

class DatabaseFactory
{
    public function create(array $dbConfig): Database
    {
        $dbHost = $dbConfig['db_host'] ?? null;
        $dbUser = $dbConfig['db_username'] ?? null;
        $dbPassword = $dbConfig['db_password'] ?? null;
        $dbPort = $dbConfig['db_port'] ?? null;
        $dbName = $dbConfig['db_database'] ?? null;

        if (!$dbName || !$dbUser || !$dbPort || !$dbHost) {
            throw new RuntimeException('Invalid credentials for connection to database.');
        }

        return new Database($dbHost, $dbPort, $dbName, $dbUser, $dbPassword);
    }
}