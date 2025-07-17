<?php

namespace App\Filament\Resources\COATagResource\Pages;

use App\Filament\Resources\COATagResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCOATag extends EditRecord
{
    protected static string $resource = COATagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
