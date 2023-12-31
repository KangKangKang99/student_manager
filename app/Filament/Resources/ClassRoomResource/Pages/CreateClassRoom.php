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
        $data['schedule'] = json_encode($data['schedule']);
        return $data;
    }
}
