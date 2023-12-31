<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClassStudent extends Model
{
    use HasFactory;

    protected $table = 'class_students';

    const STATUS_NOT_CALC = 1;
    const STATUS_CALCULATED = 2;

    public function classRoom(): BelongsTo
    {
        return $this->belongsTo(ClassRoom::class, 'class_id', 'id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

}
