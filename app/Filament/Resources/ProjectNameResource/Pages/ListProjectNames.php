<?php

namespace App\Filament\Resources\ProjectNameResource\Pages;

use App\Filament\Resources\ProjectNameResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListProjectNames extends ListRecords
{
    protected static string $resource = ProjectNameResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
