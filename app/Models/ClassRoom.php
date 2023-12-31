<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ClassRoom extends Model
{
    use HasFactory;

    protected $table = 'classes';
    const OPTION_TIME = [
        1 => 'Tiết 1 (6h45 - 8h15)',
        2 => 'Tiết 2 (8h30 - 10h00)',
        3 => 'Tiết 3 (10h15 - 11h45)',
        4 => 'Tiết 4 (13h00 - 14h30)',
        5 => 'Tiết 5 (14h45 - 16h15)',
        6 => 'Tiết 6 (16h30 - 18h00)',
        7 => 'Tiết 7 (18h15 - 19h45)',
        8 => 'Tiết 8 (20h00 - 21h30)',
    ];
    const OPTION_DAY = [
        2 => 'Thứ 2',
        3 => 'Thứ 3',
        4 => 'Thứ 4',
        5 => 'Thứ 5',
        6 => 'Thứ 6',
        7 => 'Thứ 7',
        8 => 'Chủ nhật',
    ];

    public function courseSemester(): HasOne
    {
        return $this->hasOne(CourseSemester::class, 'id', 'course_semester_id');
    }
}
