<?php

namespace App\Filament\Resources\CourseSemesterStudentResource\Pages;

use App\Filament\Resources\CourseSemesterStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCourseSemesterStudents extends ListRecords
{
    protected static string $resource = CourseSemesterStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
