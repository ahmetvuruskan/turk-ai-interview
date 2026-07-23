<?php

namespace App\Repository;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository implements IBaseRepository
{
    public function __construct(
        protected Model $model
    )
    {
    }

    public function find(int $id): ?Model
    {
        return $this->model->find($id);
    }

    public function findAll(): Collection
    {
        return $this->model->get();
    }

}
