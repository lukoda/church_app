<?php

namespace App\Filament\Administration\Resources\ChurchDistrictResource\Pages;

use App\Filament\Administration\Resources\ChurchDistrictResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use Illuminate\Support\Facades\Auth;

class EditChurchDistrict extends EditRecord
{
    protected static string $resource = ChurchDistrictResource::class;

    protected function authorizeAccess(): void
    {
        if(Auth::guard('admin')->user()->checkPermissionTo('update ChurchDistrict')){
            if(ChurchDistrict::whereId($this->getRecord()->id)->whereIn('diocese_id', Diocese::where('dinomination_id', auth()->user()->dinomination_id)->pluck('id'))->exists()){
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
        $regions = []; $districts = []; $wards = [];
        foreach($data['districts'] as $key => $district){
            // if($district[$data['regions'][$key]] == 'all'){
            //     $districts[] = District::where('region_id', Region::where('name', $data['regions'][$key])->pluck('id'))->pluck('id')->toArray();
            //     // $wards[] = Ward::where('district_id', $districts)->pluck('id')->toArray();
            // }else{
                $districts[] = District::whereIn('id', $district[$data['regions'][$key]])->pluck('id' )->toArray();
                // $wards[] = District::whereIn('id', $data['districts'])->pluck('id' )->toArray();
            // }
        }

        $churchDistrict = [];
        $regions = [];
        $regions[] = $data['regions'];
        foreach($regions[0] as $key => $region){
            $churchDistrict[] = [
                'regions' => $region,
                'districts' => $districts[$key],
                // 'wards' => $wards[$key],
            ];
        }
        
        $data['church district details'] = $churchDistrict;
        return $data;
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $regions = []; $districts = [];
        foreach($data['church district details'] as $key => $churchDistrict){
            $regions[] = $churchDistrict['regions'];
            $districts[] =[
                $regions[$key] => $churchDistrict['districts']
            ];
            $wards[] =[
                $regions[$key] => Ward::whereIn('district_id', $churchDistrict['districts'])->pluck('id'),
            ];
        }
        
        $record->update([
            'name' => $data['name'],
            'status' => $data['status'],
            'regions' => $regions,
            'districts' => $districts,
            'wards' => $wards
        ]);
    
        return $record;
    }
}
