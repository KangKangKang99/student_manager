<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\ClassStudent;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Auth;

class EditStudent extends EditRecord
{
    protected static string $resource = StudentResource::class;

    public function getHeading(): string|Htmlable
    {
        return Auth::user()->role === User::ROLE_STUDENT
            ? 'Thông tin cá nhân'
            : 'Chỉnh sửa thông tin sinh viên';
    }

    protected function getHeaderActions(): array
    {
        if (Auth::user()->role !== User::ROLE_STUDENT) {
            return [
                Actions\Action::make('reg-course')
                    ->action(function (): void {
                        $params = [
                            'student' => $this->record->id,
                        ];
                        $url = '/class-students/create?' . http_build_query($params);
                        $this->redirect($url);
                    })->label('Đăng ký học phần')->color('primary'),
                Actions\Action::make('list-course')
                    ->action(function (): void {

                        $params = [
                            'tableFilters[student][value]=' => $this->record->id,
                        ];
                        $url = '/class-students?' . http_build_query($params);
                        $this->redirect($url);
                    })->label('Học phần cá nhân')->color('success'),
                Actions\DeleteAction::make(),
            ];
        } else {
            return [];
        }
    }


    protected function mutateFormDataBeforeFill(array $data): array
    {
        $cl = [];
        $classStudents = $this->record->classStudents;
        if ($classStudents) {
            $cl = $classStudents->map(function ($item) {
                return [
                    'code' => $item->classRoom->course->code . '(' . $item->classRoom->course->name . ')' . ' - ' . $item->classRoom->code . ' - ' . $item->classRoom->semester->year . $item->classRoom->semester->semester,
                    'semester' => $item->classRoom->semester->year . '' . $item->classRoom->semester->semester,
                    'status' => match ($item->status) {
                        ClassStudent::STATUS_CALCULATED => 'Đã tổng kết',
                        ClassStudent::STATUS_NOT_CALC => 'Chưa tổng kết',
                    },
                    'score' => $item->status == ClassStudent::STATUS_CALCULATED
                        ? calcTotalScore(data_get($item, 'attendance_score', 0), data_get($item, 'midterm_score', 0), data_get($item, 'final_score', 0))
                        : '',
                    'result' => $item->status == ClassStudent::STATUS_CALCULATED
                        ? checkTotalResult(calcTotalScore(data_get($item, 'attendance_score', 0), data_get($item, 'midterm_score', 0), data_get($item, 'final_score', 0)), data_get($item, 'midterm_score', 0), data_get($item, 'final_score', 0))
                        : '',
                    'midterm' => data_get($item, 'midterm_score'),
                    'final' => data_get($item, 'final_score'),
                    'attendance' => data_get($item, 'attendance_score'),
                ];
            });
        }
        $data['class'] = $classStudents ? $cl : $data;
        return $data;
    }

    protected function getFormActions(): array
    {
        if (Auth::user()->role == User::ROLE_STUDENT) {
            return [];
        }
        return parent::getFormActions();
    }
}
