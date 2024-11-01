<?php

namespace WaHelp\Newsletter\Repository;

use WaHelp\Core\Entity\BaseRepository;

class UserNewsletterRepository extends BaseRepository
{
    public function getTableName(): string
    {
        return 'user_newsletter';
    }
}