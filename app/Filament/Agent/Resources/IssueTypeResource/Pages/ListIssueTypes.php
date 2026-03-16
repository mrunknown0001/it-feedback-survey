<?php

namespace App\Filament\Agent\Resources\IssueTypeResource\Pages;

use App\Filament\Agent\Resources\IssueTypeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListIssueTypes extends ListRecords
{
    protected static string $resource = IssueTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
