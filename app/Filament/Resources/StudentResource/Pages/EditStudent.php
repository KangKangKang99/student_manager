<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('reg-course')
                ->action(function (): void {
                    $params = [
                        'student' => $this->record->id,
                    ];
                    $url = '/course-semester-students/create?' . http_build_query($params);
                    $this->redirect($url);
                })->label('Đăng ký học phần'),
            Actions\DeleteAction::make(),
        ];
    }

}
