<?php

namespace App\Filament\Administration\Resources\AdminResource\Pages;

use App\Filament\Administration\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
            ->label(fn() => auth()->user()->hasRole('Diocese Admin') ? 'Create ChurchDistrict Admin' : (auth()->user()->hasRole('ChurchDistrict Admin') ? 'Create Church Admin' : (auth()->user()->hasRole('Dinomination Admin') ? 'Create Diocese Admin' : 'Create Church Admin'))),
        ];
    }
}
