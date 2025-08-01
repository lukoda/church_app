<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Card;
use App\Models\Church;
use App\Models\ChurchMember;
use App\Models\CardPledge;
use App\Models\Offering;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Resources\Concerns\HasTabs;
use Filament\Resources\Components\Tab;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;

class MemberCardPledges extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-hands-praying';

    protected static string $view = 'filament.pages.member-card-pledges';

    protected static ?string $navigationGroup = 'ChurchMember';

    public ?array $cards = null;

    public ?array $card_pledges = null; 

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->churchMember){
            return true;
        }else{
            return false;
        }
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            if(ChurchMember::where('id', Auth::guard('web')->user()->churchMember->id)->whereNull('card_no')->exists()){
                Notification::make()
                ->title('Please Complete Registration')
                ->body('You have not yet registered your card number, please click edit on member details page and complete registration to proceed')
                ->danger()
                ->send();
                redirect()->to('/admin/members');
            }else{
                abort_unless(static::canAccess(), 403);
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

    public static function shouldRegisterNavigation(): bool
    {
        if(Auth::guard('web')->user()->churchMember){
            return true;
        }else{
            return false;
        }
    }

    public function setCardPledges()
    {
        $this->card_pledges = auth()->user()->churchMember ? CardPledge::where('church_member_id', auth()->user()->churchMember->id)->where('church_id', auth()->user()->church_id)->where('status', 'active')->get()->toArray() : [];
    }

    public function setCards()
    {
        $this->cards = Card::where('church_id', auth()->user()->church_id)->where('card_status', 'active')->get()->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Card Offerings')
                ->headerActions([
                    CreateAction::make()
                        ->label('Enter Pledge')
                        ->model(CardPledge::class)
                        ->link()
                        ->form([
                            Grid::make(2)
                                ->schema([
                                    Select::make('card_type')
                                        ->options(function(){
                                            return Card::whereNotIn('id', CardPledge::where('church_id', auth()->user()->church_id)->where('church_member_id', auth()->user()->churchMember->id)->where('status', 'active')->pluck('card_id'))->where('card_status', 'active')->pluck('card_name','id')->toArray();
                                        })
                                        ->required(),
                                    TextInput::make('amount_pledged')
                                        ->numeric()
                                        ->required(),
                                    ])
                        ])
                        ->using(function (array $data, string $model): Model {
                            return $model::create([
                                'church_member_id' => auth()->user()->churchMember->id,
                                'church_id' => auth()->user()->church_id,
                                'card_id' => $data['card_type'],
                                'card_no' => auth()->user()->churchMember->card_no,
                                'amount_pledged' => $data['amount_pledged'],
                                'amount_completed' => 0,
                                'amount_remains' => $data['amount_pledged'],
                                'date_pledged' => now(),
                                'created_by' => auth()->user()->id,
                                'status' => 'active'
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Pledge registered')
                                ->body('Your pledge has been created successfully.'),
                        )
                        ->hidden(fn() => auth()->user()->churchMember ? (Card::whereNotIn('id', CardPledge::where('church_id', auth()->user()->church_id)->where('church_member_id', auth()->user()->churchMember->id)->where('status', 'active')->pluck('card_id'))->where('card_status', 'active')->count() > 0 ? false : true) : true),

                    Action::make('edit_pledge')
                        ->label('Edit Pledge')
                        ->link()
                        ->form([
                            Grid::make(2)
                            ->schema([
                                Select::make('card_type')
                                ->options(function(){
                                    return Card::whereIn('id', CardPledge::where('church_id', auth()->user()->church_id)->where('church_member_id', auth()->user()->churchMember->id)->where('status', 'active')->pluck('card_id'))->where('card_status', 'active')->pluck('card_name','id')->toArray();
                                })
                                ->required(),
                                TextInput::make('amount_pledged')
                                    ->numeric()
                                    ->required(),
                            ])
                        ])
                        ->action(function(array $data){
                            CardPledge::where('church_id', auth()->user()->church_id)->where('church_member_id', auth()->user()->churchMember->id)->where('card_no', auth()->user()->churchMember->card_no)->where('card_id', $data['card_type'])->where('status', 'active')->update([
                                'amount_pledged' => $data['amount_pledged'],
                                'amount_remains' => $data['amount_pledged'],
                                'amount_completed' => 0
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('Pledge Updated Successfully')
                                ->body('Your pledge has been updated successfully.'),
                        )
                        ->visible(auth()->user()->churchMember && CardPledge::where('church_id', auth()->user()->church_id)->where('church_member_id', auth()->user()->churchMember->id)->where('card_no', auth()->user()->churchMember->card_no)->where('status', 'active')->exists())
                ])
                ->query(function(){
                    if(auth()->user()->churchMember){
                        return Offering::query()->where('church_id', auth()->user()->church_id)->where('card_no', auth()->user()->churchMember->card_no)->orderBy('created_at','desc');
                    }else{
                        return Offering::query()->whereId(0);
                    }
                })
                ->columns([
                    TextColumn::make('amount_registered_on')
                    ->date(),
                    TextColumn::make('card.card_name')
                    ->label('Card Name'),
                    TextColumn::make('amount_offered'),
                    TextColumn::make('church_id')
                        ->formatStateUsing(fn($state) => Church::whereId($state)->pluck('name')[0])
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No offerings registered yet')
                ->emptyStateDescription('Once you use your card your offerings will be registered here.');
    }
}
