<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassStudentResource\Pages;
use App\Models\ClassRoom;
use App\Models\ClassStudent;
use App\Models\Course;
use App\Models\Major;
use App\Models\Semester;
use App\Models\Student;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassStudentResource extends Resource
{
    protected static ?string $model = ClassStudent::class;

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
                                        Forms\Components\Select::make('class_id')->options($cOption)->reactive()->afterStateUpdated(function (Get $get, Set $set) {
                                            $class = ClassRoom::query()->find($get('class_id'));
                                            if ($class) {
                                                $set('course_code', $class->course->code);
                                                $set('course_name', $class->course->name);
                                                $set('credit', $class->course->credit);
                                                $set('semester', $class->semester->year . $class->semester->semester);
                                            }
                                        })->afterStateHydrated(function (Get $get, Set $set, $state) {
                                            if ($state) {
                                                $class = ClassRoom::query()->find($state);
                                                if ($class) {
                                                    $set('course_code', $class->course->code);
                                                    $set('course_name', $class->course->name);
                                                    $set('credit', $class->course->credit);
                                                    $set('semester', $class->semester->year . $class->semester->semester);
                                                }
                                            }
                                        })
                                            ->required()->label(__('class code'))->disabledOn('edit')
                                            ->unique(ignoreRecord: true, modifyRuleUsing: function ($rule, callable $get) {
                                                return $rule->where('student_id', $get('student_id'));
                                            })->validationMessages(
                                                ['unique' => 'Sinh viên đã đăng ký học phần này.']
                                            ),
                                        Forms\Components\Select::make('student_id')->options($sOption)->default($defaultStd)
                                            ->required()->label(__('student code'))->disabledOn('edit'),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('attendance_score')->label(__('attendance score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create')
                                            ->disabled(fn($record) => $record?->status == ClassStudent::STATUS_CALCULATED),
                                        Forms\Components\TextInput::make('midterm_score')->label(__('midterm score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create')
                                            ->disabled(fn($record) => $record?->status == ClassStudent::STATUS_CALCULATED),
                                        Forms\Components\TextInput::make('final_score')->label(__('final score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabledOn('create')
                                            ->disabled(fn($record) => $record?->status == ClassStudent::STATUS_CALCULATED),
                                        Forms\Components\Select::make('status')->label(__('status'))->disabledOn('create')
                                            ->options([
                                                ClassStudent::STATUS_NOT_CALC => 'Chưa tổng kết',
                                                ClassStudent::STATUS_CALCULATED => 'Đã tổng kết',
                                            ])->helperText('Chọn trạng thái đã tổng kết sẽ không thể sửa điểm và điểm không nhập sẽ coi là 0')
                                            ->afterStateHydrated(function (Get $get, Set $set, $state) {
                                                $t = '';
                                                $r = '';
                                                if ((int)$get('status') === ClassStudent::STATUS_CALCULATED) {
                                                    if (!is_null($get('attendance_score')) && !is_null($get('midterm_score')) && !is_null($get('final_score'))) {
                                                        $t = calcTotalScore($get('attendance_score'), $get('midterm_score'), $get('final_score'));
                                                        $r = checkTotalResult($t);
                                                    }
                                                }
                                                $set('total_score', $t);
                                                $set('result', $r);
                                            })
                                            ->afterStateUpdated(function (Get $get, Set $set) {
                                                $t = '';
                                                $r = '';
                                                if ((int)$get('status') === ClassStudent::STATUS_CALCULATED) {
                                                    $set('attendance_score', $get('attendance_score') ?? 0);
                                                    $set('midterm_score', $get('midterm_score') ?? 0);
                                                    $set('final_score', $get('final_score') ?? 0);
                                                    if (!is_null($get('attendance_score')) && !is_null($get('midterm_score')) && !is_null($get('final_score'))) {
                                                        $t = calcTotalScore($get('attendance_score'), $get('midterm_score'), $get('final_score'));
                                                        $r = checkTotalResult($t);
                                                    }
                                                }
                                                $set('total_score', $t);
                                                $set('result', $r);
                                            })
                                            ->reactive(),
                                        Forms\Components\TextInput::make('total_score')->label(__('total score'))
                                            ->numeric()->maxValue(10)->minValue(0)->disabled()->columnSpan(1),
                                        Forms\Components\TextInput::make('result')->label(__('Kết quả'))->disabled()
                                    ]),
                                ]
                            )->description('Điểm số từ 0 đến 10. Tổng điểm = 0.1 * điểm chuyên cần + 0.3 * điểm giữa kỳ + 0.6 * điểm cuối kỳ. ')->columnSpan(2),
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
                Tables\Columns\TextColumn::make('classRoom.code')->label(__('class code')),
                Tables\Columns\TextColumn::make('classRoom.course')->label(__('Học phần'))
                    ->formatStateUsing(fn(ClassStudent $record) => $record->classRoom->course->code . ' - ' . $record->classRoom->course->name),
                Tables\Columns\TextColumn::make('classRoom.semester')->label(__('semester'))
                    ->formatStateUsing(fn(ClassStudent $record) => $record->classRoom->semester->year . $record->classRoom->semester->semester),
                Tables\Columns\TextColumn::make('student.code')->label(__('student code'))->sortable()->searchable(),
                Tables\Columns\TextColumn::make('student')->label(__('Thông tin'))
                    ->formatStateUsing(function (ClassStudent $record) {
                        $cl = '';
                        if ($record->status === ClassStudent::STATUS_CALCULATED) {
                            $total = calcTotalScore(data_get($record, 'attendance_score', 0), data_get($record, 'midterm_score', 0), data_get($record, 'final_score', 0));
                            $result = checkTotalResult($total);
                            if ($result === 'F') {
                                $cl = 'red';
                            } else {
                                $cl = 'green';
                            }
                        } else {
                            $total = 'N/A';
                            $result = 'Chưa tổng kết';
                            $cl = 'blue';
                        }
                        return "<p>Họ tên: " . $record->student->name . "</p>" .
                            "<p>Tổng điểm: " . $total . "</p>" .
                            "<p style='color:" . $cl . "'>Kết quả: " . $result . "</p>";
                    })->html(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('classRoom')->relationship('classRoom', 'code'),
                Tables\Filters\SelectFilter::make('course')->relationship('classRoom.course', 'code')->label('Mã học phần'),
                Tables\Filters\SelectFilter::make('student')->relationship('student', 'code')->label('Mã sinh viên'),

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
            'index' => Pages\ListClassStudents::route('/'),
            'create' => Pages\CreateClassStudent::route('/create'),
            'edit' => Pages\EditClassStudent::route('/{record}/edit'),
        ];
    }
}
