<?php

namespace App\Filament\Resources\OfferingResource\Pages;

use App\Filament\Resources\OfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\CardPledge;
use App\Models\Offering;
use Filament\Notifications\Notification;

class EditOffering extends EditRecord
{
    protected static string $resource = OfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update Offering')){
            if(Offering::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
            }
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to(static::getResource()::getUrl('index'));
        }

    }


    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete Offering'))
                ->visible(auth()->user()->checkPermissionTo('delete Offering')),
        ];
    }

    protected function beforeSave(): void
    {
            $old_amount = $this->record->amount_offered;
            $card_pledge = CardPledge::where('card_id', $this->data['card_type'])->where('card_no', $this->data['card_no'])->where('church_id', $this->data['church_id'])->first();
            $new_amount = ($card_pledge->amount_completed - $old_amount) + $this->data['amount_offered'];
            $card_pledge->update([
                'amount_completed' => $new_amount,
                'amount_remains' => $card_pledge->amount_pledged - $new_amount
            ]);
    }
}
