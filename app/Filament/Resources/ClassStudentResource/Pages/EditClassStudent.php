<?php

namespace App\Filament\Resources\ClassStudentResource\Pages;

use App\Filament\Resources\ClassStudentResource;
use App\Models\ClassStudent;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClassStudent extends EditRecord
{
    protected static string $resource = ClassStudentResource::class;

    protected function getHeaderActions(): array
    {
        if ($this->record->status == ClassStudent::STATUS_CALCULATED) {
            {
                return [];
            }
        }
        return [
            Actions\DeleteAction::make(),
        ];
    }

}
