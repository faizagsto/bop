<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Actions\Notification;
use Spatie\Permission\Models\Role;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function getRedirectUrl(): string
    {
        return route('filament.admin.resources.users.index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->spatieRole = $data['spatie_role'] ?? null;
        unset($data['spatie_role']);
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->spatieRole) {
            $this->record->syncRoles([$this->spatieRole]);
        }
    }
}
