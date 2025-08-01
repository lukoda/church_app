<?php

namespace App\Filament\Resources\IntroductionNoteResource\Pages;

use App\Filament\Resources\IntroductionNoteResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Notifications\Notification;

class ListIntroductionNotes extends ListRecords
{
    protected static string $resource = IntroductionNoteResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any IntroductionNote')){

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
        return [
            Actions\CreateAction::make(),
        ];
    }
}
