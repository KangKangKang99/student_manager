<?php

namespace App\Filament\Resources\StudentResource\Pages;

use App\Filament\Resources\StudentResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use JetBrains\PhpStorm\NoReturn;

class CreateStudent extends CreateRecord
{
    protected static string $resource = StudentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $user = new User(
            [
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt($data['code']),
                'role' => User::ROLE_STUDENT,
            ]
        );
        $user->save();
        return $data;
    }
}
