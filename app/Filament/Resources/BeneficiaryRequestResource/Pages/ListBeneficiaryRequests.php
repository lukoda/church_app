<?php

namespace App\Filament\Resources\BeneficiaryRequestResource\Pages;

use App\Filament\Resources\BeneficiaryRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;

class ListBeneficiaryRequests extends ListRecords
{
    protected static string $resource = BeneficiaryRequestResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any BeneficiaryRequest')){

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public function getTabs(): array
    {
        return [
            'active' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')),
            'inactive' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'inactive')),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
