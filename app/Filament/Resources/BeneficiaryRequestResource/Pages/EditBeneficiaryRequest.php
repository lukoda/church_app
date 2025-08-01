<?php

namespace App\Filament\Resources\BeneficiaryRequestResource\Pages;

use App\Filament\Resources\BeneficiaryRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Awcodes\FilamentTableRepeater\Components\TableRepeater;
use Filament\Forms\Components\TextInput;
use App\Models\BeneficiaryRequestItem;
use Filament\Notifications\Notification;
use App\Models\BeneficiaryRequest;

class EditBeneficiaryRequest extends EditRecord
{
    protected static string $resource = BeneficiaryRequestResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Church Secretary') && auth()->user()->checkPermissionTo('update BeneficiaryRequest')){
            if(BeneficiaryRequest::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
            Actions\DeleteAction::make(),
            Actions\Action::make('add_beneficiary_items')
                ->form([
                    TableRepeater::make('Beneficiary Requested Items')
                        ->schema([
                            TextInput::make('item')
                                ->datalist(function($state){
                                    return BeneficiaryRequestItem::where('item', 'like', "%{$state}%")->pluck('item');
                                })
                                ->required(),

                            TextInput::make('quantity')
                                ->numeric()
                                ->required(),

                            TextInput::make('description'),


                        ])
                        ->columnSpanFull(),
                ])
                ->action(function(array $data, $record){
                    if($record->status == 'Inactive'){
                        Notification::make()
                            ->title('Please activate request to add items')
                            ->danger()
                            ->send();
                    }else{
                        foreach($data['Beneficiary Requested Items'] as $item){
                            $items = new BeneficiaryRequestItem;
                            $items->item = $item['item'];
                            $items->quantity = $item['quantity'];
                            $items->description = $item['description'] ?? Null;
                            $items->beneficiary_request_id = $record->id;
                            $items->save(); 
                        }
    
                        Notification::make()
                            ->title('items successfully added')
                            ->success()
                            ->send();
                    }
                })
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $end_date;
        if($data['frequency'] == 'weeks'){
            if($data['request_visible_on'] == 'sunday'){
                $end_date = Carbon::now()->startOfWeek()->subDay()->addWeeks($data['weeks'])->addDays(2);
            }else{
                $end_date = Carbon::parse($data['begin_date'])->addWeeks($data['weeks'])->addDays(2);
            }
        }else if($data['frequency'] == 'days'){
            if($data['request_visible_on'] == 'sunday'){
                $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(2);
            }else{
                $end_date = Carbon::parse($data['inactive_on'])->addDays(2);
            }
        }else if($data['frequency'] == 'months'){
            if($data['request_visible_on'] == 'sunday'){
                $end_date = Carbon::now()->startOfWeek()->subDay()->addMonths($data['months'])->addDays(2);
            }else{
                $end_date = Carbon::parse($data['begin_date'])->addMonths($data['months'])->addDays(2);
            }
        }else if($data['frequency'] == 'once'){
            if($data['request_visible_on'] == 'sunday'){
                $end_date = Carbon::now()->startOfWeek()->subDay()->addDays(8);
            }else{
                $end_date = Carbon::parse($data['begin_date'])->addDays(2);
            }
        }
        
        $record->update([
            'title' => $data['title'] != $record->title ? $data['title'] : $record->title,
            'church_id' => $data['church_id'],
            'amount' => $data['amount'],
            'begin_date' => $data['begin_date'] ?? $record->begin_date,
            'request_visible_on' => $data['request_visible_on'],
            'frequency' => $data['frequency'],
            'weeks' => $data['weeks'] ?? Null,
            'months' => $data['months'] ?? Null,
            'inactive_on' => $data['inactive_on'] ?? Null,
            'amount_threshold' => $data['amount_threshold'] ?? Null,
            'status' => $data['status'],
            'purpose' => $data['purpose'],
            'supporting_documents' => $data['supporting_documents'],
            'end_date' => $record->request_visible_on != $data['request_visible_on'] ? $end_date : $record->end_date,
        ]);
    
        return $record;
    }
}
