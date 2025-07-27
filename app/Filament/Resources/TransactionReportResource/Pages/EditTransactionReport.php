<?php

namespace App\Filament\Resources\TransactionReportResource\Pages;

use App\Filament\Resources\TransactionReportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTransactionReport extends EditRecord
{
    protected static string $resource = TransactionReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
