<?php

namespace App\Filament\Resources\LogOfferingResource\Pages;

use App\Filament\Resources\LogOfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Carbon\Carbon;
use App\Models\ChurchMass;
use App\Models\ChurchAuction;
use App\Models\AuctionItem;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;

class CreateLogOffering extends CreateRecord
{
    protected static string $resource = LogOfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create LogOffering')){
            abort_unless(static::getResource()::canCreate(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }
    }

    protected function handleRecordCreation(array $data): Model
    {
        return static::getModel()::create([
            'adhoc_offering_id' => $data['adhoc_offering_id'],
            'church_mass_id' => $data['church_mass_id'],
            'user_id' => $data['user_id'],
            'church_id' => $data['church_id'],
            'amount_committee' => $data['amount_committee'],
            'has_auction' => $data['has_auction'],
            // 'amount_accountant' => $data['amount_accountant'],
            // 'approved_by' => $data['approved_by'],
            'date' => Carbon::now()->startOfWeek()->subDay()->addDays(ChurchMass::whereId($data['church_mass_id'])->pluck('day')[0])
        ]);
    }

    protected function afterCreate(): void
    {
        if($this->data['type'] != Null){
            $church_auction = new ChurchAuction;
            $church_auction->type = $this->data['type'];
            $church_auction->auction_date = Carbon::parse($this->record->date)->startOfWeek()->subDay()->addDays(ChurchMass::whereId($this->data['church_mass_id'])->pluck('day')[0]);
            $church_auction->auction_description = $this->data['auction_description'] ?? Null;
            $church_auction->log_offering = $this->record->id;
            $church_auction->status = $this->data['status'];
            $church_auction->save();
        }
    }
}
