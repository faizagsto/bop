<?php

namespace App\Filament\Resources\ApprovalStepResource\Pages;

use App\Filament\Resources\ApprovalStepResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApprovalStep extends EditRecord
{
    protected static string $resource = ApprovalStepResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
