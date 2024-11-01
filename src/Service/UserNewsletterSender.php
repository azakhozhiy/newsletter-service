<?php

namespace WaHelp\Newsletter\Service;

use PDO;
use RuntimeException;
use Throwable;
use WaHelp\Core\Database;
use WaHelp\Newsletter\Entity\Newsletter;
use WaHelp\Newsletter\Repository\UserNewsletterRepository;

class UserNewsletterSender
{
    protected int $maxRetryCount = 5;

    public function __construct(
        protected UserNewsletterRepository $userNewsletterRepository,
        protected Database $database
    ) {
    }

    public function send(Newsletter $newsletter): int
    {
        $userIterator = (new IntervalIterator(
            $this->database,
            'users'
        ))->setStep(5000);

        $userIterator->next();

        $sentCount = 0;
        $batchSize = 5000;

        foreach ($userIterator as $users) {
            foreach ($users as $user) {
                $userIds[] = $user['id'];

                if (count($userIds) >= $batchSize) {
                    $this->processBatch($newsletter, $userIds);
                    $sentCount += $batchSize;
                    $userIds = [];
                }
            }
        }

        if (!empty($userIds)) {
            $this->processBatch($newsletter, $userIds);
            $sentCount += count($userIds);
        }

        return $sentCount;
    }

    private function processBatch(Newsletter $newsletter, array $userIds, int $retryCount = 0): void
    {
        try {
            $placeholders = implode(',', array_fill(0, count($userIds), '?'));

            $query = 'SELECT user_id FROM user_newsletter WHERE newsletter_id = ? AND user_id IN ('.$placeholders.')';

            $statement = $this->userNewsletterRepository->getConnection()->prepare($query);

            $params = array_merge([$newsletter->getId()], $userIds);
            $statement->execute($params);

            $existingUserIds = $statement->fetchAll(PDO::FETCH_COLUMN);

            $userIdsToProcess = array_diff($userIds, $existingUserIds);

            $records = [];

            foreach ($userIdsToProcess as $userId) {
                $records[] = [
                    'user_id' => $userId,
                    'newsletter_id' => $newsletter->getId(),
                ];

                $this->dispatchJob($newsletter, $userId);
            }

            // Save history
            $this->userNewsletterRepository->insertBatch($records, ['user_id', 'newsletter_id']);
        } catch (Throwable $e) {
            if ($retryCount + 1 === $this->maxRetryCount) {
                throw new RuntimeException("Retry limit reached.", 0, $e);
            }

            usleep(100000);
            $this->processBatch($newsletter, $userIds, $retryCount + 1);
        }
    }

    private function dispatchJob(Newsletter $newsletter, int $userId): void
    {
        // Some async task
    }
}