<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MajorResource\Pages;
use App\Models\Major;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MajorResource extends Resource
{
    protected static ?string $model = Major::class;

    protected static ?string $navigationGroup = 'Quản lý đào tạo';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'Ngành học';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)->schema(
                            [
                                Forms\Components\TextInput::make('code')->autofocus()->required()->maxLength(20)->label(__('major code'))->placeholder('KTPM13A'),
                                Forms\Components\TextInput::make('name')->required()->maxLength(255)->label(__('major name'))->placeholder('Kỹ thuật phần mềm')
                                    ->unique(ignoreRecord: true),
                            ]
                        ),
                        Forms\Components\Section::make(__())->columnSpan(2)->schema(
                            [
                                Forms\Components\Textarea::make('note')->rows(6)->maxLength(500)->label(__('note')),
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
                Tables\Columns\TextColumn::make('code')->label(__('major code'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('major name'))
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
            'index' => Pages\ListMajors::route('/'),
            'create' => Pages\CreateMajor::route('/create'),
            'edit' => Pages\EditMajor::route('/{record}/edit'),
        ];
    }
}
