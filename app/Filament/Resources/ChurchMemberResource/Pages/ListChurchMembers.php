<?php

namespace App\Filament\Resources\ChurchMemberResource\Pages;

use App\Filament\Resources\ChurchMemberResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use App\Models\ChurchMember;
use App\Models\Church;
use Filament\Notifications\Notification;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;

class ListChurchMembers extends ListRecords
{
    protected static string $resource = ChurchMemberResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('view-any ChurchMember')){

        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public function getTabs(): array
    {
        return [
            'Unverified Church Members' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('status')->where('spiritual_information', 'complete')->whereNull('jumuiya_id'))
                ->badge(ChurchMember::query()->whereNull('status')->whereNull('jumuiya_id')->where('spiritual_information', 'complete')->where('church_id', auth()->user()->church_id)->count()),
            'Verified Church Members' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 'active')->whereNotNull('card_no')->where('church_id', auth()->user()->church_id))
                ->badge(ChurchMember::query()->where('status', 'active')->whereNotNull('card_no')->where('church_id', auth()->user()->church_id)->count()),
            'Verified New Members' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->where('is_NewMember', '1')->whereNull('card_no'))
                ->badge(ChurchMember::query()->where('is_NewMember', '1')->where('church_id', auth()->user()->church_id)->whereNull('card_no')->count()),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('Assign Member Card')
            ->form([
                Grid::make(2)
                ->schema([
                    TextInput::make('min_card_no')
                    ->label('Enter Min Card Range')
                    ->required()
                    ->reactive()
                    ->numeric(),

                    TextInput::make('max_card_no')
                    ->label('Enter Max Card Range')
                    ->required()
                    ->reactive()
                    ->numeric()

                    ])
                ])
                ->action(function(array $data): void{
                    $max_range = $data['max_card_no'];
                    $min_range = $data['min_card_no'];

                    if(blank($max_range) || blank($min_range)){
                        Notification::make()
                        ->title('All fields are required')
                        ->body('Please, set max and min range for cards to proceed')
                        ->warning()
                        ->send();

                        $action->cancel();
                    }

                    $registeredCardNos = ChurchMember::whereNotNull('card_no')->orderBy('card_no','desc')->pluck('card_no');
                    $num_to_generate = ChurchMember::whereNull('card_no')->where('is_NewMember', '1')->where('status', 'active')->orWhereNull('status')->count();
                    $count = 0;
                    $random_assigned_numbers = [];

                    for ($i = 0; $i < $num_to_generate; $i++) {
                        $randGenNo = mt_rand($min_range, $max_range);
                        if(in_array($randGenNo, $registeredCardNos->toArray()) && in_array($randGenNo, $random_assigned_numbers)){
                            $i--;
                        }else{
                            $count++;
                            $random_assigned_numbers[] = $randGenNo;
                        }
                    }


                    $unassignedChurchMembs = ChurchMember::where('is_NewMember', '1')->whereNull('card_no')->where('status', 'active')->orWhereNull('status')->get();
                    $counter = 0;
                    foreach($unassignedChurchMembs as $churchMemb){
                        $churchMemb->update([
                            'card_no' => $random_assigned_numbers[$counter]
                        ]);
                        $counter++;
                    }

                    if($count != $num_to_generate){
                        $remainingUnassignedMembers = $num_to_generate - $count;
                        Notification::make()
                        ->title('Please reset max and min range')
                        ->body('Please reset max and min range, to assign remaining ' . strval($remainingUnassignedMembers) . 'members, card range exhausted.')
                        ->warning()
                        ->send();
    
                        $action->cancel();
                    }else{
                        Notification::make()
                        ->title('Success')
                        ->body('Successfully assigned card numbers to ' . strval($num_to_generate) . ' members.')
                        ->success()
                        ->send();
                    }

                })
        ];
    }
}
