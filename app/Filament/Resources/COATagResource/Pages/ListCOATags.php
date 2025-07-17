<?php

namespace App\Filament\Resources\COATagResource\Pages;

use App\Filament\Resources\COATagResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCOATags extends ListRecords
{
    protected static string $resource = COATagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
