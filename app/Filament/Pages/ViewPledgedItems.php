<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\AuctionItem;
use App\Models\ChurchAuction;
use App\Models\AuctionItemPayment;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewPledgedItems extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-money-bill-transfer';

    protected static string $view = 'filament.pages.view-pledged-items';

    protected static ?string $navigationGroup = 'Church Pledges';

    public int $activeTab = 0;

    public int $totalPendingAuctions;

    public int $totalAuctions;

    public int $totalCompletedAuctions;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Member')){
            return true;
        }else{
            return false;
        }
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            if(Auth::guard('web')->user()->churchMember){
                if(Auth::guard('web')->user()->churchMember->whereNotNull('card_no')->count() > 0){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('You have no church card no.')
                    ->body('Please contact your Church secretary or enter pledges to be assigned card.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin');
                }
            }else{
                Notification::make()
                ->title('You have no church card no.')
                ->body('Please contact your Church secretary or enter pledges to be assigned card.')
                ->danger()
                ->send();
                redirect()->to('/admin');
            }
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('Please contact your Administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        // return auth()->user()->checkPermissionTo('view AuctionItem');
        return true;
    }

    public function setTotalPendingAuctions()
    {
        $this->totalPendingAuctions = AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->where('status', 'Pending Auction')->count();
    }

    public function setTotalAuctions()
    {
        $this->totalAuctions = AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->count();
    }

    public function setTotalCompletedAuctions()
    {
        $this->totalCompletedAuctions = AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->where('status', 'Auction Complete')->count();
    }

    public function getTabs(): array
    {
        return [
            'All',
            'Pending Auctions',
            'Completed Auctions'
        ];
    }

    public function table(Table $table) : Table
    {
        return $table
                ->heading('Auction Pledged Items')
                ->query(function(){
                    if($this->activeTab == 0){
                        return AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 1){
                        return AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->where('status', 'Pending Auction')->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 2){
                        return AuctionItem::query()->where('card_no', auth()->user()->churchMember->card_no ?? 0)->where('status', 'Auction Complete')->orderBy('created_at', 'desc');
                    }
                })
                ->groups([
                    Group::make('church_auction_id')
                        ->label('Auction Date')
                        ->getTitleFromRecordUsing(fn(Model $record) => ChurchAuction::whereId($record->church_auction_id)->pluck('auction_date')[0])
                ])
                ->groupRecordsTriggerAction(
                    fn (Action $action) => $action
                        ->button()
                        ->label('Group records'),
                )
                ->defaultGroup('church_auction_id')
                ->columns([
                    TextColumn::make('item_name'),
                    TextColumn::make('item_description')
                        ->words(6)
                        ->wrap(),
                    TextColumn::make('amount_pledged')
                        ->numeric(
                            decimalPlaces: 0,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        )
                        ->description(fn(Model $record) => "Total Payed : ".$record->amount_payed),
                    TextColumn::make('amount_remains')
                        ->label('Due Amount'),
                    TextColumn::make('payment_status')
                        ->default(function(Model $record){
                            if($record->amount_payed >= $record->amount_pledged){
                                return 'Paid';
                            }else{
                                if($record->amount_pledged > $record->amount_remains){
                                    return 'Partial Paid';
                                }else{
                                    if($record->amount_payed == 0){
                                        return 'Unpaid';
                                    }
                                }
                            }
                        })
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            'Paid' => 'success',
                            'Partial Paid' => 'warning',
                            'Unpaid' => 'danger',
                        })
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No Owed Pledged Items')
                ->emptyStateDescription('Once you have pending pledges will appear here.')
                ->actions([
                    Action::make('add_payment')
                        ->fillForm(fn(Model $record): array => [
                            'amount' => $record->amount_remains,
                        ])
                        ->form([
                            Grid::make(2)
                                ->schema([
                                    TextInput::make('amount')
                                        ->helperTEXT('Default amount is due amount for item.')
                                        ->required()
                                        ->numeric(),
                                    Select::make('payment_mode')
                                        ->reactive()
                                        ->options([
                                            'Mobile' => 'Mobile',
                                            'Bank' => 'Bank'
                                        ])
                                        ->required(),
                                        ]),
                            Section::make('Bank Transaction Details')
                                ->description('Please verify details for ease of verification.')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                            Select::make('account_provider')
                                                ->options([
                                                    'crdb' => 'CRDB',
                                                    'nmb' => 'NMB',
                                                    'maendeleo' => 'MAENDELEO BANK'
                                                ])
                                                ->required(),
                                            TextInput::make('bank_branch_name')
                                                ->required(),
                                        ]),
                                    TextInput::make('bank_transaction_id')
                                        ->helperText('Please provide transaction_id or account payee name as appear in uploaded receipt.')
                                        ->required(),
                                ])
                                ->visible(function(Get $get){
                                    if($get('payment_mode') == 'Bank'){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }),
                            Section::make('Mobile Transaction Details')
                                ->description('Please verify details for ease of verification.')
                                ->schema([
                                    Grid::make(2)
                                        ->schema([
                                                Select::make('mobile_account_provider')
                                                    ->options([
                                                        'm-pesa' => 'MPESA',
                                                        'tigopesa' => 'TIGOPESA',
                                                        'halopesa' => 'HALOPESA',
                                                        'airtelmoney' => 'AIRTELMONEY',
                                                        'ttclpesa' => 'TTCLPESA',
                                                        'ezypesa' => 'EZYPESA'
                                                    ])
                                                    ->required(),
                                                    
                                                TextInput::make('mobile_transaction_id')
                                                    ->helperText('Please provide mobile transaction id or name of agent as appear in uploaded receipt.')
                                                    ->required(),
                                        ])
                            ])
                            ->visible(function(Get $get){
                                if($get('payment_mode') == 'Mobile'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),
                            FileUpload::make('receipt_picture')
                                ->downloadable()
                                ->previewable()
                                ->openable()
                                ->columnSpan('full')
                                ->disk('auctionPledgeReceipts')
                                ->visible(function(Get $get){
                                    if(blank($get('payment_mode'))){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }),
                            
                        ])
                        ->action(function(array $data){
                            $auction_item_payments = new AuctionItemPayment;
                            $auction_item_payments->amount_payed = $data['amount'];
                            $auction_item_payments->payment_mode = $data['payment_mode'];
                            $auction_item_payments->account_provider = $data['payment_mode'] == 'Bank' ? $data['account_provider'] : Null;
                            $auction_item_payments->bank_branch_name = $data['payment_mode'] == 'Bank' ? $data['bank_branch_name'] : Null;
                            $auction_item_payments->bank_transaction_id = $data['payment_mode'] == 'Bank' ? $data['bank_transaction_id'] : Null;
                            $auction_item_payments->mobile_account_provider = $data['payment_mode'] == 'Mobile' ? $data['mobile_account_provider'] : Null;
                            $auction_item_payments->mobile_transaction_id = $data['payment_mode'] == 'Mobile' ? $data['mobile_transaction_id'] : Null;
                            $auction_item_payments->receipt_picture = $data['receipt_picture'];
                            $auction_item_payments->verification_status = 'Unverified';
                            $auction_item_payments->save();

                            Notification::make()
                                ->title('Payment sent successfully')
                                ->body('Await for verification of payments.')
                                ->success()
                                ->send();
                        })
                        ->visible(function(Model $record){
                            if($record->amount_remains <= 0){
                                return false;
                            }else{
                                if(auth()->user()->checkPermissionTo('create AuctionItemPayment')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }
                        })
                ]);
    }
}
