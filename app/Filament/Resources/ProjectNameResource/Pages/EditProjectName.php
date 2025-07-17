<?php

namespace App\Filament\Resources\ProjectNameResource\Pages;

use App\Filament\Resources\ProjectNameResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProjectName extends EditRecord
{
    protected static string $resource = ProjectNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
