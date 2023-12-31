<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CourseResource\Pages;
use App\Filament\Resources\CourseResource\RelationManagers;
use App\Models\Course;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CourseResource extends Resource
{
    protected static ?string $model = Course::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?int $navigationSort = 3;

    public static function getModelLabel(): string
    {
        return 'Học phần';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)->schema(
                            [
                                Forms\Components\TextInput::make('code')->autofocus()->required()->maxLength(20)->label(__('course code'))->placeholder('HP001')
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label(__('course name'))->placeholder('Mạng máy tính')
                                    ->unique(ignoreRecord: true),
                                Forms\Components\TextInput::make('credit')->numeric()->required()->maxValue(10)->label(__('credit'))->placeholder('4'),
                            ]
                        ),
                        Forms\Components\Section::make(__('note'))->columnSpan(1)->schema(
                            [
                                Forms\Components\Textarea::make('note')->rows(6)->maxLength(500)->label(__(''))->placeholder('Học phần tiêu chuẩn ngành kỹ thuật phần mềm')
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
                Tables\Columns\TextColumn::make('code')->label(__('course code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('course name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('credit')->label(__('credit'))
                    ->searchable()
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
            'index' => Pages\ListCourses::route('/'),
            'create' => Pages\CreateCourse::route('/create'),
            'edit' => Pages\EditCourse::route('/{record}/edit'),
        ];
    }
}
