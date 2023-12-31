<?php

namespace App\Filament\Resources\CourseSemesterStudentResource\Pages;

use App\Filament\Resources\CourseSemesterStudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCourseSemesterStudent extends EditRecord
{
    protected static string $resource = CourseSemesterStudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
