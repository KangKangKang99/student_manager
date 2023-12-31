<?php

namespace App\Filament\Resources\ClassRoomResource\Pages;

use App\Filament\Resources\ClassRoomResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassRoom extends EditRecord
{
    protected static string $resource = ClassRoomResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['schedule'] = json_decode($data['schedule'], true);
        $courseSemester = $this->record->courseSemester;
        $data['course_id'] = $courseSemester->course_id;
        $data['semester_id'] = $courseSemester->semester_id;
        return $data;
    }
}
