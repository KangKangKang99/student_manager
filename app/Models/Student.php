<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory;

    const STATUS_STUDYING = 1;
    const STATUS_FINISHED = 2;
    const STATUS_GRADUATED = 3;

    const GENDER_MALE = 1;
    const GENDER_FEMALE = 2;
    const GENDER_OTHER = 3;

    function major(): BelongsTo
    {
        return $this->belongsTo(Major::class);
    }

    function classStudents(): HasMany
    {
        return $this->hasMany(ClassStudent::class, 'student_id', 'id');
    }

    function avgScore()
    {
        $cl = $this->classStudents()->where('status', ClassStudent::STATUS_CALCULATED)->get();
        $arr = [];
        foreach ($cl as $item) {
            $arr[] = [
                'score' => calcTotalScore(data_get($item, 'attendance_score', 0), data_get($item, 'midterm_score', 0), data_get($item, 'final_score', 0)),
                'course' => $item->classRoom->course->code,
            ];
        }
        if (count($arr) == 0) {
            return 'N/A';
        }
        $temp = [];
        foreach ($arr as $item) {
            $course = $item['course'];
            $score = $item['score'];
            if (array_key_exists($course, $temp)) {
                $temp[$course] = max($temp[$course], $score);
            } else {
                $temp[$course] = $score;
            }
        }

        $sc = 0;
        foreach ($temp as $course => $score) {
            $sc += (float)$score;
        }
        return round($sc / count($temp), 2);
    }

    function schoolarship()
    {
        $cl = $this->classStudents()->where('status', ClassStudent::STATUS_CALCULATED)->get();
        if (count($cl) == 0) {
            return 0;
        }
        $count = 0;
        foreach ($cl as $item) {
            $score = calcTotalScore(data_get($item, 'attendance_score', 0), data_get($item, 'midterm_score', 0), data_get($item, 'final_score', 0));
            if (checkTotalResult($score) == 'F') {
                return 0;
            } elseif (checkTotalResult($score) == 'A') {
                $count++;
            }
        }
        return $count >= 2;
    }
}
