<?php

namespace App\Filament\Resources\LogOfferingResource\Pages;

use App\Filament\Resources\LogOfferingResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use App\Models\ChurchAuction;
use App\Models\ChurchMember;
use App\Models\ChurchMass;
use App\Models\AuctionItem;
use App\Models\AuctionItemPayment;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use App\Models\LogOffering;

class EditLogOffering extends EditRecord
{
    protected static string $resource = LogOfferingResource::class;

    protected function authorizeAccess(): void
    {
        if(auth()->user()->checkPermissionTo('update LogOffering')){
            if(LogOffering::whereId($this->getRecord()->id)->where('church_id', auth()->user()->church_id)->exists()){
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
            Actions\DeleteAction::make()
                ->disabled(! auth()->user()->checkPermissionTo('delete LogOffering'))
                ->visible(auth()->user()->checkPermissionTo('delete LogOffering')),
            Actions\Action::make('add_pledges')
                ->label('Add Pledges')
                ->form([
                    Repeater::make('auctioned_items')
                    ->columnSpan('full')
                    ->collapsible()
                    ->schema([
                        Section::make('auction_item_details')
                            ->columns(3)
                            ->schema([
                                Checkbox::make('is_church_member')
                                    ->inline('false')
                                    ->default(true)
                                    ->reactive(),
        
                                Select::make('card_no')
                                    ->searchable()
                                    ->live(onBlur: true)
                                    ->options(function(){
                                        return ChurchMember::whereNotNull('card_no')->pluck('card_no','card_no');
                                    })
                                    ->visible(function(Get $get){
                                        if($get('is_church_member')){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    })
                                    ->afterStateUpdated(function(Set $set, $state){
                                        if(! blank($state)){
                                            $set('member_name', ChurchMember::where('card_no', $state)->pluck('full_name'));
                                            $set('member_full_name', ChurchMember::where('card_no', $state)->pluck('full_name'));
                                        }
                                    }),

                                TextInput::make('member_name')
                                    ->disabled()
                                    ->visible(function(Get $get){
                                        if($get('card_no')){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),
                                
                                Hidden::make('member_full_name'),
        
                                TextInput::make('name')
                                    ->label('Names')
                                    ->visible(function(Get $get){
                                        if($get('is_church_member')){
                                            return false;
                                        }else{
                                            return true;
                                        }
                                    }),
        
                                TextInput::make('phone_no')
                                    ->tel()
                                    ->maxLength(13)
                                    ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/')
                                    ->helperText('+255*********')
                                    ->required(),
                                    
                                TextInput::make('item_name')
                                    ->required(),
        
                                TextInput::make('item_description')
                                    ->columnSpan(2)
                                    ->nullable(),
                                
                                Hidden::make('registered_on')
                                    ->default(now()),
        
                                Hidden::make('status')
                                    ->default('Pending Auction'),
        
                                Hidden::make('created_by')
                                    ->default(auth()->user()->id),
                                ]),

                        Checkbox::make('has_payments')
                                ->reactive()
                                ->default(true),

                        Section::make('auctioned_item_payment_details')
                            ->columns(3)
                            ->schema([
                                TextInput::make('amount_pledged')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->afterStateUpdated(function($state, Set $set){
                                        if(blank($state)){
                                            $set('amount_remains', 0);
                                        }else{
                                            $set('amount_remains', $state);

                                        }
                                    }),
        
                                TextInput::make('amount_payed')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(0)
                                    ->afterStateUpdated(function(Get $get, Set $set, $state){
                                        if(blank($state)){
                                            $set('amount_remains', ($get('amount_pledged') - 0));
                                        }else{
                                            $set('amount_remains', ($get('amount_pledged') - $state));
                                        }
                                    }),
        
                                TextInput::make('amount_remains')
                                    ->disabled(),
                            ])
                            ->visible(fn(Get $get) => $get('has_payments') == true ? true : false)
                            ->live(onBlur: true)
        
                    ])
                ])
                ->action(function(array $data){
                    foreach($data['auctioned_items'] as $item){
                        $auction_item = new AuctionItem;
                        $auction_item->item_name = $item['item_name'];
                        $auction_item->item_description = $item['item_description'];
                        $auction_item->is_church_member = $item['is_church_member'];
                        $auction_item->card_no = $item['is_church_member'] ? $item['card_no'] : Null;
                        $auction_item->name = $item['is_church_member'] ? $item['member_full_name'] : $item['name'];
                        $auction_item->phone_no = $item['phone_no'];
                        $auction_item->registered_on = $item['registered_on'];
                        $auction_item->status = ($item['amount_pledged'] - $item['amount_payed']) > 0 ? $item['status'] : 'Auction Complete';
                        $auction_item->created_by = $item['created_by'];
                        $auction_item->amount_pledged = $item['amount_pledged'];
                        $auction_item->amount_payed = $item['amount_payed'];
                        $auction_item->amount_remains = $item['amount_pledged'] - $item['amount_payed'];
                        $auction_item->church_auction_id = $this->record->church_auction->id;
                        $auction_item->save();

                        if($auction_item->status == $item['status']){
                            $this->record->church_auction->update([
                                'status' => 'Pending Auction'
                            ]);
                        }
                    }

                    Notification::make()
                        ->title('Auction items have been added successfully')
                        ->success()
                        ->send();
                })
                ->disabled($this->record->church_auction == Null ? true : ! auth()->user()->checkPermissionTo('create AuctionItem'))
                ->visible($this->record->church_auction == Null ? false : auth()->user()->checkPermissionTo('create AuctionItem')),

                Actions\Action::make('add_cash')
                    ->label(fn() => $this->record->church_auction->status == 'Auction Complete' ? 'All Auction Items Payed' : 'Add Pledge Payments')
                    ->form([
                        Repeater::make('auctioned_item_payments')
                        ->columnSpan('full')
                        ->collapsible()
                        ->schema([
                                Select::make('auction_item_buyer')
                                    ->label('Search Person')
                                    ->reactive()
                                    ->searchable()
                                    ->getSearchResultsUsing(function ($search){
                                        return AuctionItem::where('church_auction_id', $this->record->church_auction->id)
                                                    ->where('status', 'Pending Auction')
                                                    ->where('name', 'like', "%{$search}%")
                                                     ->orWhere('phone_no', 'like', "%{$search}%")
                                                     ->orWhere('card_no', 'like',"%{$search}%" )
                                                     ->pluck('item_name', 'id');
                                    })
                                    ->getOptionLabelUsing(fn($value): ?string => AuctionItem::find($value)->where('status', 'Pending Auction')->name),
                                Grid::make(3)
                                ->schema([
                                    TextInput::make('name')
                                    ->disabled(),

                                Select::make('payment_mode')
                                    ->options([
                                        'cash' => 'Cash',
                                        'receipt' => 'Receipt'
                                    ])
                                    ->required(),

                                TextInput::make('amount_payed')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),

                                TextInput::make('description')
                                    ->nullable(),

                                DatePicker::make('date_registered')
                                    ->label('date')
                                    ->minDate(function() {
                                        if($this->record->church_auction != Null){
                                            return Carbon::parse($this->record->church_auction->created_at)->subDay();
                                        }
                                    })
                                    ->default(now())
                                    ->required(),

                                ]),
                               
                                Hidden::make('church_id')
                                    ->default(auth()->user()->church_id),

                                Hidden::make('registered_by')
                                    ->default(auth()->user()->id)
                        ])
                    ])
                    ->action(function(array $data){
                        foreach($data['auctioned_item_payments'] as $payment){
                            $item_payment = new AuctionItemPayment;
                            $item_payment->payment_mode = $payment['payment_mode'];
                            $item_payment->amount_payed = $payment['amount_payed'];
                            $item_payment->date_registered = $payment['date_registered'];
                            $item_payment->description = $payment['description'] ?? Null;
                            $item_payment->church_id = $payment['church_id'];
                            $item_payment->auction_item_id = $payment['auction_item'];
                            $item_payment->registered_by = $payment['registered_by'];
                            $item_payment->save();

                            $auction_item = AuctionItem::whereId($payment['auction_item'])->first();
                            $auction_item->amount_payed += $payment['amount_payed'];
                            $auction_item->amount_remains = $auction_item->amount_pledged - $auction_item->amount_payed;
                            $auction_item->status = $auction_item->amount_remains > 0 ? 'Pending Auction' : 'Auction Complete';
                            $auction_item->save();
                        }
                        if(! $this->record->church_auction->auction_items->where('status', 'Pending Auction')->first()){
                            $this->record->church_auction->update([
                                'status' => 'Auction Complete'
                            ]);
                        }

                        Notification::make()
                            ->title('Auction item payments added successfully')
                            ->success()
                            ->send();
                    })
                    ->disabled(fn() => $this->record->church_auction->status == 'Auction Complete' ? true : (AuctionItem::whereId($this->record->church_auction->id)->count() > 0 ? auth()->user()->checkPermissionTo('create AuctionItemPayment') : false))
                    ->visible($this->record->church_auction == Null ? false : (AuctionItem::whereId($this->record->church_auction->id)->count() > 0 ? auth()->user()->checkPermissionTo('create AuctionItemPayment') : false)),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['type'] = $this->record->church_auction == NUll ? '' : $this->record->church_auction->type;
        $data['auction_description'] = $this->record->church_auction == NUll ? '' : $this->record->church_auction->auction_description;
    
        return $data;
    }

    protected function afterSave(): void
    {
        if($this->record->church_auction != Null){
            $this->record->church_auction->update([
                'type' => $this->data['type'],
                'description' => $this->data['description']
            ]);
        }else{
            $church_auction = new ChurchAuction;
            $church_auction->type = $this->data['type'];
            $church_auction->auction_date = Carbon::now()->startOfWeek()->subDay()->addDays(ChurchMass::whereId($this->data['church_mass_id'])->pluck('day')[0]);
            $church_auction->auction_description = $this->data['auction_description'] ?? Null;
            $church_auction->log_offering = $this->record->id;
            $church_auction->status = $this->data['status'];
            $church_auction->save();
        }
    }
}
