<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Student extends Model
{
    use HasFactory, SoftDeletes, HasUuids;


    protected $fillable = [
        'name',
        'surname',
        'number',
        'grade',
        'registration_code',
        'parent_id',
        'deleted_at',
    ];


    public function parent(): ?BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id', 'id');
    }

}
