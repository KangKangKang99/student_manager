<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ManagerResource\Pages;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Psy\Util\Str;

class ManagerResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Quản trị hệ thống';


    public static function getModelLabel(): string
    {
        return __('manager');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(3)->schema(
                    [
                        Forms\Components\Section::make(__('basic information'))->columnSpan(2)->schema(
                            [
                                Forms\Components\TextInput::make('name')->autofocus()->required()->maxLength(255)->label(__('name')),
                                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255)->label(__('email'))
                                    ->unique(ignoreRecord: true),
                                Forms\Components\Select::make('role')->options([
                                    User::ROLE_SUPER_ADMIN => __('super admin'),
                                    User::ROLE_ADMIN => __('admin'),
                                ])->required()->label(__('role'))->maxWidth(1 / 2),
                                Forms\Components\TextInput::make('password')->required()->minLength(8)->maxLength(20)->label(__('password'))
                                    ->default(generatePassword())
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('autoGeneratePassword')
                                            ->icon('heroicon-m-sparkles')
                                            ->requiresConfirmation()
                                            ->action(function (Set $set, $state) {
                                                $p = generatePassword();
                                                $set('password', $p);
                                            })->label(__('auto generate password'))
                                    )
                                ->hidden(fn($record) => $record?->exists),
                            ]
                        ),
                        Forms\Components\Section::make(__('status'))->columnSpan(1)->schema(
                            [
                                Forms\Components\Toggle::make('status')->default(1)->label(__('status'))
                                    ->helperText(__('this user will be use for manager')),
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
                Tables\Columns\TextColumn::make('email')->label(__('email'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('name')->label(__('name'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')->label(__('role'))
                    ->badge()
                    ->color(fn(User $user) => match ($user->role) {
                        User::ROLE_SUPER_ADMIN => 'success',
                        User::ROLE_ADMIN => 'primary',
                        default => 'danger',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        User::ROLE_SUPER_ADMIN => __('super admin'),
                        User::ROLE_ADMIN => __('admin'),
                        default => __('unknown'),
                    })
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
            'index' => Pages\ListManagers::route('/'),
            'create' => Pages\CreateManager::route('/create'),
            'edit' => Pages\EditManager::route('/{record}/edit'),
        ];
    }
}
