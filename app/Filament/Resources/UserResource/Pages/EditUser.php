<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Spatie\Permission\Models\Role;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

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

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $this->spatieRole = $data['spatie_role'] ?? null;
        unset($data['spatie_role']);
        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->spatieRole) {
            $this->record->syncRoles([$this->spatieRole]);
        }
    }
}
