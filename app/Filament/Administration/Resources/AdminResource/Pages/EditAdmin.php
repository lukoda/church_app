<?php

namespace App\Filament\Administration\Resources\AdminResource\Pages;

use App\Filament\Administration\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Admin;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->after(function(){
                $admin = Admin::whereId($this->getRecord()->id)->first();
                if($this->getRecord()->church_level == 'Diocese'){
                    $admin->removeRole('Diocese Admin');
                }else if($this->getRecord()->church_level == 'ChurchDistrict'){
                    $admin->removeRole('ChurchDistrict Admin');
                }else if($this->getRecord()->church_level == 'Parish'){
                    $admin->removeRole('Parish Admin');
                }
            }),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

}
