<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface IBaseRepository
{
    public function find(int $id): ?Model;
    public function findAll(): Collection;
}
