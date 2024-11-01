<?php

namespace WaHelp\Newsletter\Service;

use RuntimeException;
use Throwable;
use WaHelp\Core\Exception\Http\RequestValidationException;
use WaHelp\Core\Http\Request;
use WaHelp\Newsletter\Repository\UserRepository;

class UserFileImporter
{
    protected int $maxFileSize;

    public function __construct(protected UserRepository $userRepository)
    {
        $this->maxFileSize = 50 * 1024 * 1024;
    }

    public function setMaxFileSize(int $maxFileSize): static
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    public function importByRequest(Request $request): int
    {
        try {
            $this->validateImportFile($file = $request->file('users'));
        } catch (Throwable $e) {
            throw new RequestValidationException(
                $request, "File validation error. {$e->getMessage()}", 0, $e
            );
        }

        return $this->importUsers($file);
    }

    protected function importUsers(array $file): int
    {
        $batchSize = 1000;
        $users = [];

        if (($handle = fopen($file['tmp_name'], 'rb')) === false) {
            throw new RuntimeException("Unable to open the file.");
        }

        $imported = 0;
        $userColumns = ['number', 'name'];
        $connection = $this->userRepository->getConnection();
        $insertFn = function (array $users, array $userColumns) {
            if (!$this->userRepository->insertBatch($users, $userColumns)) {
                throw new RuntimeException("Batch insert failed.");
            }
        };

        try {
            $connection->beginTransaction();

            while (($data = fgetcsv($handle)) !== false) {
                if (!isset($data[0], $data[1])) {
                    continue;
                }

                $users[] = [
                    'number' => str_replace(' ', '', $data[0]),
                    'name' => $data[1],
                ];

                if (count($users) === $batchSize) {
                    $insertFn($users, $userColumns);
                    $imported += $batchSize;
                    $users = [];
                }
            }

            if (!empty($users)) {
                $insertFn($users, $userColumns);
                $imported += count($users);
            }

            $connection->commit();
        } catch (Throwable $e) {
            $connection->rollBack();

            throw new RuntimeException("User import failed: ".$e->getMessage(), 0, $e);
        } finally {
            fclose($handle);
        }

        return $imported;
    }

    protected function validateImportFile(?array $file): void
    {
        if (!$file) {
            throw new RuntimeException("File `users` not found.");
        }

        if ($file['size'] > $this->maxFileSize) {
            throw new RuntimeException("File size exceeds the maximum limit of 50MB.");
        }

        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

        if (strtolower($extension) !== 'csv') {
            throw new RuntimeException("Invalid file format. Only CSV files are allowed.");
        }
    }
}