<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseSemesterStudentResource\Pages;
use App\Filament\Resources\CourseSemesterStudentResource\RelationManagers;
use App\Models\ClassRoom;
use App\Models\CourseSemesterStudent;
use App\Models\Major;
use App\Models\Student;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseSemesterStudentResource extends Resource
{
    protected static ?string $model = CourseSemesterStudent::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Quản lý học tập';

    public static function getModelLabel(): string
    {
        return 'Học phần sinh viên';
    }

    protected static ?int $navigationSort = 8;

    public static function form(Form $form): Form
    {
        $isStudentParam = request()->query('student');
        $defaultStd = $isStudentParam ? Student::query()->find($isStudentParam)?->id : null;
        $mOptions = Major::query()->orderBy('code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => "{$item->code} - {$item->name}"];
            })->toArray();
        $sOption = Student::query()->orderBy('code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => "{$item->code} - {$item->name}"];
            })->toArray();
        $cOption = ClassRoom::query()->orderBy('code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => "{$item->code} - {$item->name}"];
            })->toArray();
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)
                            ->schema(
                                [
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Select::make('class')->options($cOption)->reactive()->afterStateUpdated(function (Get $get, Set $set) {
                                            $class = ClassRoom::query()->find($get('class'));
                                            if ($class) {
                                                $courseSemester = $class->courseSemester;
                                                $set('course_code', $courseSemester->course->code);
                                                $set('course_name', $courseSemester->course->name);
                                                $set('credit', $courseSemester->course->credit);
                                                $set('semester', $courseSemester->semester->year . $courseSemester->semester->semester);
                                            }
                                        })
                                            ->required()->label(__('class code')),
                                        Forms\Components\Select::make('student')->options($sOption)->default($defaultStd)
                                            ->required()->label(__('student code'))->disabledOn('edit'),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('attendance_score')->label(__('attendance score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create'),
                                        Forms\Components\TextInput::make('midterm_score')->label(__('midterm score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create'),
                                        Forms\Components\TextInput::make('final_score')->label(__('final score'))->columnSpan(2)
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create'),
                                        Forms\Components\TextInput::make('total_score')->label(__('total score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create')->columnSpan(2),
                                    ]),
                                ]
                            ),
                        Forms\Components\Section::make(__('Thông tin mã lớp'))->columnSpan(1)->schema(
                            [
                                Forms\Components\TextInput::make('course_code')->label('Mã học phần')->disabled(),
                                Forms\Components\TextInput::make('course_name')->label('Tên học phần')->disabled(),
                                Forms\Components\TextInput::make('credit')->label('Số tín chỉ')->disabled(),
                                Forms\Components\TextInput::make('semester')->label('Học kỳ')->disabled(),
                            ]
                        ),
                    ]
                )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCourseSemesterStudents::route('/'),
            'create' => Pages\CreateCourseSemesterStudent::route('/create'),
            'edit' => Pages\EditCourseSemesterStudent::route('/{record}/edit'),
        ];
    }
}
