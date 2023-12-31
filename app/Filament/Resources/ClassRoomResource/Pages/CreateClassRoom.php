<?php

namespace App\Filament\Resources\ClassRoomResource\Pages;

use App\Filament\Resources\ClassRoomResource;
use App\Models\CourseSemester;
use Filament\Resources\Pages\CreateRecord;
use JetBrains\PhpStorm\NoReturn;

class CreateClassRoom extends CreateRecord
{
    protected static string $resource = ClassRoomResource::class;

    #[NoReturn]
    public function mutateFormDataBeforeCreate(array $data): array
    {
        $courseSemester = CourseSemester::query()->where('course_id', $data['course_id'])
            ->where('semester_id', $data['semester_id'])->first();
        if ($courseSemester) {
            $data['course_semester_id'] = $courseSemester->id;
        } else {
            $data['course_semester_id'] = CourseSemester::query()->create([
                'course_id' => $data['course_id'],
                'semester_id' => $data['semester_id'],
            ])->id;
        }
        unset($data['course_id']);
        unset($data['semester_id']);
        $data['schedule'] = json_encode($data['schedule']);
        return $data;
    }
}
