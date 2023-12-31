<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SemesterResource\Pages;
use App\Models\Semester;
use Carbon\Carbon;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\DatePicker;
use Illuminate\Validation\Rules\Unique;

class SemesterResource extends Resource
{
    protected static ?string $model = Semester::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?int $navigationSort = 2;

    public static function getModelLabel(): string
    {
        return 'Học kỳ';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)->schema(
                            [
                                Forms\Components\Grid::make(2)->schema(
                                    [
                                        Forms\Components\Select::make('year')->options([
                                            Carbon::now()->year => Carbon::now()->year,
                                            Carbon::now()->year + 1 => Carbon::now()->year + 1,
                                        ])->required()->label(__('year'))->maxWidth(1 / 2)
                                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, callable $get) {
                                                return $rule
                                                    ->where('semester', $get('semester'))
                                                    ->where('year', $get('year'));
                                            })
                                            ->validationMessages([
                                                'unique' => 'Học kỳ năm :input đã tồn tại.',
                                            ]),
                                        Forms\Components\Select::make('semester')->options([
                                            1 => 1,
                                            2 => 2,
                                            3 => 3,
                                        ])->required()->label(__('semester'))->maxWidth(1 / 2),
                                    ]),
                                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label(__('semester name'))->placeholder('Học kỳ 1 năm 2023')
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Grid::make(2)->schema(
                                    [
                                        Forms\Components\DatePicker::make('start_date')->required()->label(__('start date'))
                                            ->default(Carbon::now()),
                                        Forms\Components\DatePicker::make('end_date')->required()->label(__('end date'))
                                            ->default(Carbon::now()->addMonths(4))->after('start_date'),
                                    ])
                            ]
                        )
                    ]
                )
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label(__('semester code'))
                    ->state(function (Semester $semester) {
                        return $semester->year . $semester->semester;
                    }),
                Tables\Columns\TextColumn::make('name')->label(__('semester name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('year')->label(__('semester name'))
                    ->searchable()->hidden()
                    ->sortable(),
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
            'index' => Pages\ListSemesters::route('/'),
            'create' => Pages\CreateSemester::route('/create'),
            'edit' => Pages\EditSemester::route('/{record}/edit'),
        ];
    }
}
