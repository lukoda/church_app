<?php

namespace App\Filament\Resources\OfferingResource\Pages;

use App\Filament\Resources\OfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Carbon\Carbon;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Offering;
use Filament\Notifications\Notification;

class ListOfferings extends ListRecords
{
    protected static string $resource = OfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Offering')){

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
            'all' => Tab::make('This Week Offerings')
                ->badge(Offering::query()->whereDate('amount_registered_on', Carbon::now()->startOfWeek(Carbon::SUNDAY))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('amount_registered_on', Carbon::now()->startOfWeek(Carbon::SUNDAY))),
            'active' => Tab::make('Last Week Offerings')
                ->badge(Offering::query()->whereDate('amount_registered_on', Carbon::now()->startOfWeek(Carbon::SUNDAY)->subDays(14))->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('amount_registered_on', Carbon::now()->startOfWeek(Carbon::SUNDAY)->subDays(14))),
            'inactive' => Tab::make('This Year Offerings')
                ->badge(Offering::query()->whereYear('amount_registered_on', Carbon::now()->year)->count())
                ->modifyQueryUsing(fn (Builder $query) => $query->whereYear('amount_registered_on', Carbon::now()->year)),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            OfferingResource\Widgets\OfferingOverview::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make() 
                ->disabled(! auth()->user()->checkPermissionTo('create Offering'))           
                ->visible(auth()->user()->checkPermissionTo('create Offering')),
        ];
    }
}
