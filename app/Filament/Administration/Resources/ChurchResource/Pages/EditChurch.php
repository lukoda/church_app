<?php

namespace App\Filament\Administration\Resources\ChurchResource\Pages;

use App\Filament\Administration\Resources\ChurchResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Church;
use App\Models\ChurchDistrict;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditChurch extends EditRecord
{
    protected static string $resource = ChurchResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->hasRole('Dinomination Admin') && $this->getrecord()->church_type != 'dinomination')
        {
            Notification::make()
                ->title('Access Denied, can only be edited by respective '.$this->getrecord()->church_type.' admin.')
                ->success()
                ->send();

                redirect()->to(static::getResource()::getUrl('index'));
        }else if(auth()->user()->hasRole('Diocese Admin') && $this->getrecord()->church_type != 'diocese'){
            Notification::make()
                ->title('Access Denied, can only be edited by respective '.$record->church_type.' admin.')
                ->success()
                ->send();

                redirect()->to(static::getResource()::getUrl('index'));
        }else if(auth()->user()->hasRole('ChurchDistrict Admin') && $this->getrecord()->church_type != 'sub_parish'){
            Notification::make()
            ->title('Access Denied, can only be edited by respective church admin')
            ->success()
            ->send();

            redirect()->to(static::getResource()::getUrl('index'));
        }else if(Auth::guard('admin')->user()->checkPermissionTo('update Church')){

            if(Auth::guard('admin')->user()->hasRole('Parish Admin') && Church::whereId($this->getrecord()->id)->orwhere('parent_church', auth()->user()->church_id)->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin') && Church::whereId($this->getRecord()->id)->where('church_district_id', auth()->user()->church_district_id)->where('church_type', 'parish')->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && Church::whereId($this->getRecord()->id)->whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->where('church_type', 'diocese')->exists()){
                abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
            }else if(auth()->user()->hasRole('Dinomination Admin') && $this->getrecord()->church_type == 'dinomination' && Church::whereId($this->getRecord()->id)->exists()){
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


        // if(Auth::guard('admin')->user()->checkPermissionTo('update Church')){
        //     if(Auth::guard('admin')->user()->hasRole('Parish Admin') && Church::whereId($this->getrecord()->id)->where('parent_church', auth()->user()->church_id)->exists()){
        //         abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
        //     }else if(Auth::guard('admin')->user()->hasRole('ChurchDistrict Admin') && Church::whereId($this->getRecord()->id)->where('church_district_id', auth()->user()->church_district_id)->exists()){
        //         abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
        //     }else if(Auth::guard('admin')->user()->hasRole('Diocese Admin') && Church::whereId($this->getRecord()->id)->whereIn('church_district_id', ChurchDistrict::where('diocese_id', auth()->user()->diocese_id)->pluck('id'))->exists()){
        //         abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
        //     }else if(auth()->user()->hasRole('Dinomination Admin') && $this->getrecord()->church_type != 'dinomination'){
        //         abort_unless(static::getResource()::canEdit($this->getRecord()), 403);
        //     }
        //     else{
        //         Notification::make()
        //         ->title('Page Not Found')
        //         ->body('Sorry, the requested page does not exist.')
        //         ->danger()
        //         ->send();
        //     }
        // }else{
        //     Notification::make()
        //     ->title('Access Denied')
        //     ->body('Please contact your administrator.')
        //     ->danger()
        //     ->send();
        //     redirect()->to(static::getResource()::getUrl('index'));
        // }

    }


    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if(auth()->user()->hasRole('Dinomination Admin')){
            $data['church_district'] = $data['church_district_id'];
        }

        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->action(function(Actions\DeleteAction $action, Church $record){
                    if($record->church_members->count() > 0){
                        Notification::make()
                            ->warning()
                            ->title('Church has members')
                            ->body('Can\'t delete church has members')
                            ->persistent()
                            ->send();

                        $action->cancel();
                    }else{
                        $this->record->delete();

                        Notification::make()
                        ->warning()
                        ->title('Success')
                        ->body('Church Deleted Successfully')
                        ->persistent()
                        ->send();

                        redirect()->to(static::getResource()::getUrl('index'));
                    }

                }),
        ];
    }
}
