<?php

namespace App\Filament\Administration\Resources\DioceseResource\Pages;

use App\Filament\Administration\Resources\DioceseResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Diocese;
use App\Models\District;
use App\Models\Region;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditDiocese extends EditRecord
{
    protected static string $resource = DioceseResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('update Diocese')){
            if(Diocese::whereId($this->getRecord()->id)->where('dinomination_id', auth()->user()->dinomination_id)->exists()){
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
        ];
    }
    
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $regions = []; $districts = [];
        foreach($data['districts'] as $key => $district){
            $regions[] = $district[$data['regions'][$key]];
            $districts[] = District::whereIn('id', $district[$data['regions'][$key]])->pluck('id' )->toArray();
        }

        $diocese = [];
        $regions = $data['regions'];
        foreach($regions as $key => $region){
            $diocese[] = [
                'regions' => $region,
                'districts' => $districts[$key],
                'all_districts' => $data['all_districts'][$key]
            ];
        }
        $data['diocese details'] = $diocese;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {

        $regions = []; $districts = []; $all_districts = [];
        foreach($data['diocese details'] as $key => $diocese){
            $regions[] = $diocese['regions'];
            $all_districts[] = $diocese['all_districts'];
            if($diocese['all_districts'] == true){
                $districts[] = [
                    $regions[$key] => District::whereRegionId(Region::whereName($regions[$key])->pluck('id'))->pluck('id'),
                ];
            }else{
                $districts[] =[
                    $regions[$key] => $diocese['districts']
                ];
            }
        }

        $record->update([
            'name' => $data['name'],
            'status' => $data['status'],
            'regions' => $regions,
            'districts' => $districts,
            'all_districts' => $all_districts
        ]);
    
        return $record;
    }
}
