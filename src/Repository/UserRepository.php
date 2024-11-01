<?php

namespace WaHelp\Newsletter\Repository;

use WaHelp\Core\Entity\BaseRepository;

class UserRepository extends BaseRepository
{
    public function getTableName(): string
    {
        return 'users';
    }
}