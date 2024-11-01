<?php

namespace WaHelp\Newsletter\Repository;

use WaHelp\Core\Entity\BaseRepository;
use WaHelp\Newsletter\Entity\Newsletter;
use WaHelp\Newsletter\Enum\NewsletterStatusEnum;

class NewsletterRepository extends BaseRepository
{
    public function getTableName(): string
    {
        return 'newsletter';
    }

    public function create(array $data): Newsletter
    {
        $id = $this->insertOne($data);

        return (new Newsletter(
            $data['name'],
            $data['text'],
            NewsletterStatusEnum::from($data['status'])
        ))->setId($id);
    }
}