<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ClassRoomResource\Pages;
use App\Filament\Resources\ClassRoomResource\RelationManagers;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Semester;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ClassRoomResource extends Resource
{
    protected static ?string $model = ClassRoom::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?int $navigationSort = 4;

    public static function getNavigationLabel(): string
    {
        return 'Mở lớp học phần';
    }

    public static function getModelLabel(): string
    {
        return 'Lớp học phần';
    }

    public static function form(Form $form): Form
    {
        $sOptions = Semester::query()->orderBy('year', 'desc')
            ->orderBy('semester', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->year . $item->semester];
            })->toArray();
        $cOptions = Course::query()->orderBy('code')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id => $item->code];
            })->toArray();
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)->schema(
                            [
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('code')
                                        ->required()->label(__('class code'))->unique(ignoreRecord: true),
                                    Forms\Components\TextInput::make('name')
                                        ->required()->label(__('class name')),
                                ]),
                                Forms\Components\Repeater::make('schedule')->schema([
                                    Forms\Components\Grid::make(2)->schema([
                                        Forms\Components\Select::make('day')->options(ClassRoom::OPTION_DAY)->required()->label(__('day')),
                                        Forms\Components\Select::make('time')->options(ClassRoom::OPTION_TIME)->required()->label(__('time')),
                                    ]),
                                ])->label(__('schedule')),
                            ]
                        ),
                        Forms\Components\Section::make(__('Học phần'))->columnSpan(1)->schema(
                            [
                                Forms\Components\Select::make('semester_id')->options($sOptions)
                                    ->required()->label(__('semester'))->disabledOn('edit'),
                                Forms\Components\Select::make('course_id')->options($cOptions)
                                    ->required()->label(__('course code'))->disabledOn('edit'),
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
                Tables\Columns\TextColumn::make('code')->label(__('class code'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('courseSemester.course.code')->label(__('course code'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('courseSemester.semester')->label(__('semester'))
                    ->state(fn($record) => $record->courseSemester->semester->year . $record->courseSemester->semester->semester),
                Tables\Columns\TextColumn::make('name')->label(__('class name'))->searchable()->sortable(),
                Tables\Columns\TextColumn::make('schedule')->label(__('schedule'))->searchable()->sortable()
                    ->formatStateUsing(function ($state) {
                        $schedule = json_decode($state, true);

                        if (!empty($schedule)) {
                            return collect($schedule)->map(function ($item) {
                                return ClassRoom::OPTION_DAY[(int)$item['day']] . ' ' . ClassRoom::OPTION_TIME[(int)$item['time']];
                            })->implode('<br> ');
                        }

                        return '';
                    })->html(),
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
            'index' => Pages\ListClassRooms::route('/'),
            'create' => Pages\CreateClassRoom::route('/create'),
            'edit' => Pages\EditClassRoom::route('/{record}/edit'),
        ];
    }
}
