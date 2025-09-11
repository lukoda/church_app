<?php

namespace App\Filament\Resources\CommitteeResource\Pages;

use App\Filament\Resources\CommitteeResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Notifications\Notification;
use App\Models\ChurchMember;
use App\Models\Jumuiya;
use App\Models\User;

class ManageCommittees extends ManageRecords
{
    protected static string $resource = CommitteeResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any Committee')){
            if(! ChurchMember::where('church_id', auth()->user()->church_id)->where('status', 'active')->where('id','!=',auth()->user()->churchMember->id)->exists()){
                Notification::make()
                ->title('No Registered Church Member')
                ->body('Please register church member to be assigned role as Church Elder')
                ->danger()
                ->send();
                redirect()->to('/admin');
            }else if(Jumuiya::where('status', 'active')->count() <= 0){
                Notification::make()
                ->title('No Registered Jumuiya')
                ->body('Please register a jumuiya to assign church elder a jumuiya.')
                ->danger()
                ->send();
                redirect()->to('/admin');
            }

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    protected function getHeaderActions(): array
    {
        if(auth()->user()->checkPermissionTo('create Committee')){
            return [
                Actions\CreateAction::make()
                ->after(function($record){
                    $churchMember = ChurchMember::where('id', $record->church_member_id)->pluck('user_id')[0];

                    //assign role committee member (church elder)
                    $usrRole = User::whereId($churchMember)->first();
                    $usrRole->assignRole('Committee Member');
                })
            ];
        }else{
            return [];
        }
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        dd($data);
    
        return $data;
    }
    

}
