<?php

namespace App\Filament\Resources\OfferingResource\Pages;

use App\Filament\Resources\OfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use App\Models\CardPledge;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class CreateOffering extends CreateRecord
{
    protected static string $resource = OfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('create Offering')){
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
        $models = []; $total_amount =0;
        foreach($data['cards'] as $card){
            $models[] = static::getModel()::create([
                'card_no' => $data['card_no'],
                'card_type' => $card['card_type'],
                'church_id' => $data['church_id'],
                'amount_offered' => $card['amount_offered'],
                'amount_registered_on' => Carbon::now()->englishDayOfWeek == 'Sunday' ? Carbon::now()->dateString() : Carbon::now()->startOfWeek(CARBON::SUNDAY),
                'created_by' => $data['created_by'],
                'updated_by' => $data['updated_by'],

            ]);
           $card_pledge = CardPledge::where('card_id', $card['card_type'])->where('card_no', $data['card_no'])->where('church_id', $data['church_id'])->first();
           if($card_pledge !== Null){
            $new_amount = $card_pledge->amount_completed + $card['amount_offered'];
            $card_pledge->update([
             'amount_completed' => $new_amount,
             'amount_remains' => $card_pledge->amount_pledged - $new_amount
            ]);
           }
        }
        return $models[0];
    }
}
