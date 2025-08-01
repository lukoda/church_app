<?php

namespace App\Filament\Resources\ChurchMemberResource\Pages;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ChurchMember;
use App\Models\Church;
use Filament\Notifications\Notification;

class ListChurchMembers extends ListRecords
{
    protected static string $resource = ChurchMemberResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any ChurchMember')){

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
            'Unverified Church Members' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('status'))
                ->badge(ChurchMember::query()->whereNull('status')->where('church_id', auth()->user()->church_id)->count()),
            'Verified Church Members' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active'))
                ->badge(ChurchMember::query()->where('status', 'active')->where('church_id', auth()->user()->church_id)->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
