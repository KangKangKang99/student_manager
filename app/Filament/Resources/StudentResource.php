<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StudentResource\Pages;
use App\Filament\Resources\StudentResource\RelationManagers;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Major;
use App\Models\Semester;
use App\Models\Student;
use App\Models\User;
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
use Illuminate\Support\Facades\Auth;

class StudentResource extends Resource
{
    protected static ?string $model = Student::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Quản lý học tập';
    protected static ?int $navigationSort = 7;

    public static function getModelLabel(): string
    {
        return 'Sinh viên';
    }

    public static function form(Form $form): Form
    {

        $mOptions = Major::query()->orderBy('code')
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
                                        Forms\Components\TextInput::make('code')
                                            ->required()->label(__('student code'))->unique(ignoreRecord: true)->length(8)->numeric()->readOnly()
                                            ->helperText(__('generate student code')),
                                        Forms\Components\TextInput::make('name')
                                            ->required()->label(__('student name')),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('email')
                                            ->suffixAction(
                                                Forms\Components\Actions\Action::make('autoGenerateEmail')
                                                    ->icon('heroicon-m-sparkles')
                                                    ->requiresConfirmation()
                                                    ->action(function (Get $get, Set $set) {
                                                        $code = $get('code');
                                                        $name = $get('name');
                                                        if ($code && $name) {
                                                            $converted = fn($str) => strtolower(replaceAccentedCharacters($str));
                                                            $arrName = explode(' ', $name);
                                                            $last = $converted(array_pop($arrName));
                                                            $n = $last . array_reduce($arrName, function ($carry, $item) use ($converted) {
                                                                    return $carry . substr($converted($item), 0, 1);
                                                                }, '') . $code . '@school.edu.vn';
                                                            $set('email', $n);
                                                        }
                                                    })->label(__('generate student code'))

                                            )->helperText(__('generate student email'))
                                            ->required()->label(__('email'))->unique(ignoreRecord: true),
                                        Forms\Components\TextInput::make('phone')
                                            ->required()->label(__('phone')),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('address')
                                            ->required()->label(__('address')),
                                        Forms\Components\DatePicker::make('dob')
                                            ->required()->label(__('birthday')),
                                    ]),
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\TextInput::make('cid')
                                            ->required()->label(__('cid'))->unique(ignoreRecord: true),
                                        Forms\Components\Select::make('gender')->options(
                                            [
                                                Student::GENDER_MALE => 'Nam',
                                                Student::GENDER_FEMALE => 'Nữ',
                                            ]
                                        )->required()->label(__('gender')),
                                    ]),
                                ]
                            ),
                        Forms\Components\Section::make(__('Ngành học'))->columnSpan(1)->schema(
                            [
                                Forms\Components\Select::make('major_id')->options($mOptions)
                                    ->required()->label(__('major'))->disabledOn('edit'),
                                Forms\Components\Select::make('status')->options(
                                // condition edit or create
                                    fn($record) => $record?->exists
                                        ? [
                                            Student::STATUS_STUDYING => 'Đang học',
                                            Student::STATUS_FINISHED => 'Đã tốt nghiệp',
                                            Student::STATUS_GRADUATED => 'Đã ra trường',
                                        ]
                                        : [
                                            Student::STATUS_STUDYING => 'Đang học',
                                        ]
                                )->required()->label(__('status')),
                                Forms\Components\DatePicker::make('admission_date')
                                    ->required()->label(__('admission date'))->disabledOn('edit')
                                    ->afterStateUpdated(function (Get $get, Set $set) {
                                        $date = $get('admission_date');
                                        if ($date) {
                                            $year = Carbon::create($date)->year;
                                            $newestId = Student::query()->orderBy('id', 'desc')->first()->id ?? 0;
                                            $n = $year . str_pad($newestId + 1, 4, '0', STR_PAD_LEFT);
                                            $set('code', $n);
                                        }
                                    })->reactive(),
                            ]
                        ),
                        Forms\Components\Section::make(__('Học phần cá nhân'))->columnSpan(3)->schema(
                            [
                                Forms\Components\Repeater::make('class')->label('')->disabled()->visibleOn('edit')->schema(
                                    [
                                        Forms\Components\Grid::make(5)->schema(
                                            [
                                                Forms\Components\TextInput::make('code')->label(__('Học phần - lớp - học kỳ'))->columnSpan(2),
                                                Forms\Components\TextInput::make('status')->label(__('status')),
                                                Forms\Components\TextInput::make('score')->label(__('Total score')),
                                                Forms\Components\TextInput::make('result')->label(__('Kết quả')),
                                            ]
                                        ),
                                    ]
                                ),
                            ]
                        )->visibleOn('edit'),
                    ]
                )
            ])->disabled(fn($record) => Auth::user()->role == User::ROLE_STUDENT);
    }

    public static function table(Table $table): Table
    {
        $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label(__('student code'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('student name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('student.major')->label(__('major'))->state(fn($record) => $record->major->name . ' - ' . $record->major->code),
                Tables\Columns\TextColumn::make('information')->label(__('Thông tin'))
                    ->state(function ($record) {
                        $statusColor = match ($record->status) {
                            Student::STATUS_STUDYING => 'green',
                            Student::STATUS_FINISHED => 'red',
                            Student::STATUS_GRADUATED => 'blue',
                        };
                        $statusText = match ($record->status) {
                            Student::STATUS_STUDYING => 'Đang học',
                            Student::STATUS_FINISHED => 'Đã tốt nghiệp',
                            Student::STATUS_GRADUATED => 'Đã ra trường',
                        };
                        $schoolarShip = $record->schoolarShip() ? 'Có' : 'Không';
                        $clColor = $record->schoolarShip() ? 'green' : '';
                        return
                            '<span class="mr-1">Email : </span>' . $record->email . '<br>' .
                            '<span class="mr-1">Số điện thoại : </span>' . $record->phone . '<br>' .
                            '<span class="mr-1">Trung bình môn : </span>' . $record->avgScore() . '<br>' .
                            '<span class="mr-1" style="color:' . $clColor . '">Học bổng : </span>' . $schoolarShip . '<br>' .
                            '<div style="color:' . $statusColor . '"><span class="mr-1">Trạng thái: </span>' . $statusText . '<br></div>';
                    })->html(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('major_id')->options(
                    Major::query()->orderBy('code')
                        ->get()
                        ->mapWithKeys(function ($item) {
                            return [$item->id => "{$item->code} - {$item->name}"];
                        })->toArray()
                )->label(__('major'))->multiple(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
        if (Auth::user()->role == User::ROLE_STUDENT) {
            $table->modifyQueryUsing(function (Builder $query) {
                return $query->where('email', Auth::user()->email);
            });
        } else {
            $table->actions([
                Tables\Actions\EditAction::make()
            ]);
        }
        return $table;
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
            'index' => Pages\ListStudents::route('/'),
            'create' => Pages\CreateStudent::route('/create'),
            'edit' => Pages\EditStudent::route('/{record}/edit'),
        ];
    }
}
