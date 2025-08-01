<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Illuminate\Http\Request;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Contracts\HasInfolists;
use Filament\Infolists\Infolist;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\BeneficiaryRequest;
use App\Models\BeneficiaryRequestItem;
use App\Models\BeneficiaryRequestItemPayment;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Grid as FormGrid;
use Filament\Forms\Get;
use Filament\Forms\Components\FileUpload;
use LaraZeus\Accordion\Infolists\Accordion;
use LaraZeus\Accordion\Infolists\Accordions;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\Actions\Action as RecordAction;
use Filament\Support\Enums\Alignment;
use App\Models\BeneficiaryRequestItemPledge;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ViewBeneficiaryDetails extends Page implements HasInfolists, HasTable
{
    use InteractsWithInfolists,InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-beneficiary-details';

    public int $record = 0;

    private int $passed_record_param = 0;

    public ?array $donationDetails;

    public int $activeTab = 0;

    public int $requested_items = 0;

    public int $requested_item_pledges = 0;

    public float $requested_amount_pledges = 0;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Member') && Auth::guard('web')->user()->checkPermissionTo('view Beneficiary')){
            return true;
        }else{
            return false;
        }
    }

    public function setRecordParam()
    {
        $id = $_REQUEST['record'];
        if(is_numeric($id)){
            $this->record = $id;
        }else{
            Notification::make()
            ->title('Page Not Found')
            ->body('Sorry, the requested page does not exist.')
            ->danger()
            ->send();
            redirect()->to('/admin/beneficiary-request'); 
            // redirect()->to('admin/church-announcements');
            // Notification::make()
            //     ->danger()
            //     ->title('The record doesn\'t exist')
            //     ->body('Please select valid record in table.')
            //     ->send();
        }
    }

    // public static function getUrl(array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null): string
    // {
    //     if (blank($panel) || Filament::getPanel($panel)->hasTenancy()) {
    //         $parameters['tenant'] ??= ($tenant ?? Filament::getTenant());
    //     }
    //     if(is_numeric($parameters['record'])){
    //         $this->passed_record_param = $parameters['record'];
    //     }
    //     return route(static::getRouteName($panel), $parameters, $isAbsolute);
    // }

    public function mountCanAuthorizeAccess(): void
    {
        $this->setRecordParam();
        if(static::canAccess()){
            if(BeneficiaryRequest::whereId($this->passed_record_param)->where('begin_date','<=',now())->where('church_id', Auth::guard('web')->user()->church_id)->exists()){
                abort_unless(static::canAccess(), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
                redirect()->to('/admin/beneficiary-request');
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

    public function updatedActiveTab()
    {
        $this->resetTable();
    }

    public function getTabs(): array
    {
        return [
            'Requested Items',
            'Requested Item Pledges',
            'Requested Amount Pledges'
        ];
    }

    public function setRequestedItems()
    {
        $this->requested_items = BeneficiaryRequestItem::where('beneficiary_request_id',$this->record)->count();
    }

    public function setRequestedItemPledges()
    {
        $this->requested_item_pledges = BeneficiaryRequestItem::where('beneficiary_request_id',$this->record)->has('beneficiary_request_item_pledges')->count();
    }

    public function setRequestedAmountPledges()
    {
        $this->requested_amount_pledges = BeneficiaryRequest::whereId($this->record)->has('request_amount_pledges')->count();
    }

    public function setRecord($id)
    {
        if(is_numeric($id)){
            $this->record = $id;
        }else{
            Notification::make()
            ->title('Page Not Found')
            ->body('Sorry, the requested page does not exist.')
            ->danger()
            ->send();
            redirect()->to('/admin/beneficiary-request');
            // Notification::make()
            //     ->danger()
            //     ->title('The record doesn\'t exist')
            //     ->body('Please select valid record in table.')
            //     ->send();
        }
    }

    public function setDonationDetails()
    {
        $details = [];
        $beneficiary_payment_details = Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->pluck('beneficiary_id')[0])->where('church_id', auth()->user()->church_id)->first();
        $mobileInstanceCount = 0;
        $bankInstanceCount = 0;
        foreach($beneficiary_payment_details->payment_mode as $key => $payment_detail){
            $details[] = [
                'payment_mode' => $payment_detail,
                'account_name' => $payment_detail == 'Mobile' ? Null : $beneficiary_payment_details->account_name[$bankInstanceCount] ?? Null,
                'account_provider' => $payment_detail == 'Mobile' ? Null : $beneficiary_payment_details->account_provider[$bankInstanceCount] ?? Null,
                'account_no' => $payment_detail == 'Mobile' ? Null : $beneficiary_payment_details->account_no[$bankInstanceCount] ?? Null,
                'mobile_no' => $payment_detail == 'Bank' ? Null : $beneficiary_payment_details->mobile_no[$mobileInstanceCount] ?? Null,
                'mobile_account_name' => $payment_detail == 'Bank' ? Null : $beneficiary_payment_details->mobile_account_name[$mobileInstanceCount] ?? Null,
                'mobile_account_provider' => $payment_detail == 'Bank' ? Null : $beneficiary_payment_details->mobile_account_provider[$mobileInstanceCount] ?? Null,
            ];
            if($payment_detail == 'Mobile'){
                $mobileInstanceCount++;
            }else if($payment_detail == 'Bank'){
                $bankInstanceCount++;
            }
        }

        $this->donationDetails[] = $details;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function generateAccordions(): array
    {
        $accordions = [];
        $beneficiary_requests = BeneficiaryRequest::whereId($this->record)->first();
        foreach($beneficiary_requests->supporting_documents as $key => $beneficiary_request){
            $accordions[] =  Accordion::make('documents')
                                ->columns()
                                ->schema([
                                    ViewEntry::make('document')
                                        ->view('infolists.components.pdf-viewer', ['document' => 'beneficiaryRequestDocuments/'.$beneficiary_request, 'key' => 'document-'.$key])
                                ]);
        }
        return $accordions;
    }

    public function dynamicTables(): array
    {
        if($this->activeTab == 0){
            return [
                TextColumn::make('item')
                    ->description(fn(Model $record) => $record->description),
                TextColumn::make('quantity'),
                TextColumn::make('items_pledged')
                    ->numeric()
                    ->default(function(Model $record){
                        if(BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->exists()){
                            return BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->sum('item_quantity_pledged');
                        }else{
                            return 0;
                        }
                    }),
                TextColumn::make('completed_pledges')
                    ->numeric()
                    ->default(function(Model $record){
                        if(BeneficiaryRequestItemPayment::whereIn('item_id', BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->pluck('id'))->exists()){
                            return BeneficiaryRequestItemPayment::whereIn('item_id', BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->pluck('id'))->sum('item_quantity_payed');
                        }else{
                            return 0;
                        }
                    }),
                TextColumn::make('payment_status')
                    ->default(function(Model $record){
                        if(BeneficiaryRequestItemPayment::whereIn('item_id', BeneficiaryRequestItemPledge::where('request_item_id', $record->id)->pluck('id'))->where('verification_status', 'unverified')->exists()){
                            return "Pending Payment";
                        }else{
                            if(BeneficiaryRequestItemPayment::whereIn('item_id', BeneficiaryRequestItemPledge::where('request_item_id', $record->id)->pluck('id'))->whereNot('verification_status', 'unverified')->exists()){
                                return "Pledge Payment Completed";
                            }else{
                                return "Unpaid Pledge";
                            }
                        }
                    }),
                ];
        }else if($this->activeTab == 1){
                return [
                    TextColumn::make('item_quantity_pledged')
                        ->numeric(),
                    TextColumn::make('item_quantity_complete')
                        ->numeric(),
                    TextColumn::make('payment_status')
                        ->default(function(Model $record){
                            if($record->item_quantity_complete >= $record->item_quantity_pledged){
                                return "Payment Complete";
                            }else if($record->item_quantity_completed > 0 && $record->item_quantity_pledged > $record->item_quantity_completed){
                                return "Partial Payed";
                            }else{
                                return "Pending Pledge";
                            }
                        }),
                    TextColumn::make('verification_status')
                        ->badge()
                        ->default(function (BeneficiaryRequestItemPledge $record){
                            return BeneficiaryRequestItemPayment::where('item_id', $record->id)->pluck('verification_status');
                        })
                        ->color(fn(string $state): string => match ($state){
                            'verified' => 'success',
                            'unverified' => 'danger'
                        })
                    ];
        }else if($this->activeTab = 2){
            return [
                TextColumn::make('amount_pledged')
                    ->numeric(),
                TextColumn::make('amount_completed')
                    ->numeric(),
                TextColumn::make('payment_status')
                    ->default(function(Model $record){
                        if($record->amount_completed >= $record->amount_pledged){
                            return "Payment Complete";
                        }else if($record->amount_completed > 0 && $record->amount_pledged > $record->amount_completed){
                            return "Partial Payed";
                        }else{
                            return "Pending Pledge";
                        }
                    }),
                TextColumn::make('verification_status')
                    ->badge()
                    ->default(function (BeneficiaryRequestItemPledge $record){
                        return BeneficiaryRequestItemPayment::where('item_id', $record->id)->pluck('verification_status');
                    })
                    ->color(fn(string $state): string => match ($state){
                        'verified' => 'success',
                        'partial paid' => 'warning',
                        'unpaid' => 'secondary',
                        'unverified' => 'danger'
                    })
                ];
        }
    }

    protected function makeInfolist(): Infolist
    {
        return Infolist::make($this)
                 ->record(BeneficiaryRequest::whereId($this->record)->first())
                 ->state([
                    'Payment Details' => $this->donationDetails[0]
                 ])
                 ->schema([
                        Tabs::make('Beneficiary Request Details')
                            ->tabs([
                                Tabs\Tab::make('Request Details')
                                ->schema([
                                    Grid::make(4)
                                        ->schema([
                                            TextEntry::make('title')
                                                ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('title')[0]),

                                            TextEntry::make('amount')
                                                ->label('Requested Amount')
                                                ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('amount')[0])
                                                ->numeric(
                                                    decimalPlaces: 0,
                                                    decimalSeparator: '.',
                                                    thousandsSeparator: ',',
                                                )
                                                ->prefixAction(
                                                    RecordAction::make('add_amount_pledge')
                                                        ->icon('fas-hands-praying')
                                                        ->form([
                                                            TextInput::make('amount_pledge')
                                                                ->numeric()
                                                                ->required(),

                                                            Hidden::make('request_item_id')
                                                                ->default($this->record)
                                                        ])
                                                        ->action(function(array $data){
                                                            $amount_pledge = new BeneficiaryRequestItemPledge;
                                                            $amount_pledge->amount_pledged = $data['amount_pledge'];
                                                            $amount_pledge->amount_completed = 0;
                                                            $amount_pledge->request_item_id = $data['request_item_id'];
                                                            $amount_pledge->user_id = auth()->user()->id;
                                                            $amount_pledge->payment_status = 'unpaid';
                                                            $amount_pledge->save();

                                                            Notification::make()
                                                                ->title('Pledge Submitted Successfully.')
                                                                ->body('We thank you for your contribution. God Bless You.')
                                                                ->success()
                                                                ->send();
                                                        })
                                                        ->visible(auth()->user()->checkPermissionTo('create BeneficiaryRequestItemPledge'))
                                                ),
                                            TextEntry::make('end_date')
                                                ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('end_date')[0])
                                                ->date(),
                                            // TextEntry::make('beneficiary_type')
                                            //     ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_type')[0]),
                                        ]),

                                    Section::make('purpose of request')
                                        ->schema([
                                            TextEntry::make('purpose')
                                                ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('purpose')[0])
                                                ->markdown()
                                                ->columnSpan('full')
                                        ]),

                                    Section::make('supporting_documents')
                                        ->description('Still On progress')
                                        ->schema([
                                            Accordions::make()
                                            ->isolated()                                
                                            ->accordions($this->generateAccordions())
                                        ])

                                        ]),
                                        
                                Tabs\Tab::make('Beneficiary Details')
                                    ->schema([
                                        Grid::make(5)
                                            ->schema([
                                                TextEntry::make('beneficiary_name')
                                                    ->default(Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_id')[0])->pluck('name')[0]),
                                                TextEntry::make('beneficiary_type')
                                                    ->default(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_type')[0]),
                                                TextEntry::make('group_leader_name')
                                                    ->default(Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_id')[0])->pluck('group_leader_name')[0])
                                                    ->visible(function(){
                                                        if(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_type')[0] == 'group'){
                                                            return true;
                                                        }else{
                                                            return false;
                                                        }
                                                    }),
                                                TextEntry::make('gender')
                                                    ->default(Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_id')[0])->pluck('gender')[0]),
                                                TextEntry::make('phone_no')
                                                    ->default(Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_id')[0])->pluck('phone_no')[0]),
                                                TextEntry::make('status')
                                                    ->default(Beneficiary::whereId(BeneficiaryRequest::whereId($this->record)->where('church_id', auth()->user()->church_id)->pluck('beneficiary_id')[0])->pluck('status')[0])
                                                    ->badge()                                                    
                                                    ->color(fn (string $state): string => match ($state) {
                                                        'Active' => 'success',
                                                        'Inactive' => 'warning',
                                                    })
                                                ]),

                                                RepeatableEntry::make('Payment Details')
                                                    ->schema([
                                                        Grid::make(4)
                                                            ->schema([
                                                                TextEntry::make('payment_mode'),
                                                                TextEntry::make('account_name')
                                                                    ->visible(function($state){
                                                                        if(blank($state)){
                                                                            return false;
                                                                        }else{
                                                                            return true;
                                                                        }
                                                                    }),
                                                                TextEntry::make('account_provider')
                                                                ->visible(function($state){
                                                                    if(blank($state)){
                                                                        return false;
                                                                    }else{
                                                                        return true;
                                                                    }
                                                                }),
                                                                TextEntry::make('account_no')
                                                                ->visible(function($state){
                                                                    if(blank($state)){
                                                                        return false;
                                                                    }else{
                                                                        return true;
                                                                    }
                                                                }),
                                                                TextEntry::make('mobile_no')
                                                                ->visible(function($state){
                                                                    if(blank($state)){
                                                                        return false;
                                                                    }else{
                                                                        return true;
                                                                    }
                                                                }),
                                                                TextEntry::make('mobile_account_name')
                                                                ->visible(function($state){
                                                                    if(blank($state)){
                                                                        return false;
                                                                    }else{
                                                                        return true;
                                                                    }
                                                                }),
                                                                TextEntry::make('mobile_account_provider')
                                                                ->visible(function($state){
                                                                    if(blank($state)){
                                                                        return false;
                                                                    }else{
                                                                        return true;
                                                                    }
                                                                })
                                                            ])
                                                    ])
                                            ]),
                                 ]),



                ]);
    }

    public function table(Table $table): Table
    {
        // $entry = $this->setRecord(session('record'));
        return $table
                 ->heading('Requested Items')
                 ->headerActions([

                 ])
                 ->query(function(){
                    if($this->activeTab == 0){
                        return BeneficiaryRequestItem::query()->where('beneficiary_request_id',$this->record)->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 1){
                        $beneficiary_request_item = BeneficiaryRequestItem::where('beneficiary_request_id',$this->record)->pluck('id');
                        if(count($beneficiary_request_item) > 0){
                            return BeneficiaryRequestItemPledge::query()->whereIn('pledged_item_id', $beneficiary_request_item)->orderBy('created_at', 'desc');
                        }else{
                            return BeneficiaryRequestItemPledge::query()->whereId(0);
                        }
                    }else if($this->activeTab == 2){
                        return BeneficiaryRequestItemPledge::query()->where('request_item_id', $this->record)->orderBy('created_at', 'desc');
                    }
                 })
                 ->columns($this->dynamicTables())
                 ->emptyStateIcon('fas-hands-praying')
                 ->emptyStateHeading('No Beneficiary Items Registered')
                 ->emptyStateDescription('Once items are registered will be displayed here.')
                 ->actions([
                    Action::make('add_pledge')
                        ->label('Add Pledge')
                        ->fillForm(fn(BeneficiaryRequestitem $record) : array => [
                            'items_pledged_already' => BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->where('user_id', auth()->user()->id)->sum('item_quantity_pledged'),
                            'pending_pledged_items' => BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->where('user_id', auth()->user()->id)->ordoesntHave('pledge_payments')->orWhereHas('pledge_payments', function(Builder $query){
                                $query->where('verification_status', 'unverified');
                            })->sum('item_quantity_pledged'),
                            'payed_pledged_items' => BeneficiaryRequestItemPledge::where('pledged_item_id', $record->id)->where('user_id', auth()->user()->id)->whereHas('pledge_payments', function(Builder $query){
                                $query->where('verification_status', 'verified');
                            })->sum('item_quantity_pledged'),
                        ])
                        ->form([
                            FormGrid::make(3)
                                ->schema([
                                    TextInput::make('items_pledged_already')
                                        ->disabled(),
                                    TextInput::make('pending_pledged_items')
                                        ->disabled()
                                        ->helperText('Pending items still owed.'),
                                    TextInput::make('payed_pledged_items')
                                        ->disabled()
                                        ->helperText('Items already payed.'),
                                    TextInput::make('item_quantity_pledged')
                                        ->numeric()
                                        ->required(),
                                    Hidden::make('pledged_item_id')
                                        ->default(fn(Model $record) => $record->id)
                                ]),                            
                        ])
                        ->action(function(array $data, BeneficiaryRequestitem $record){
                            $pledged_item = new BeneficiaryRequestItemPledge;
                            $pledged_item->item_quantity_pledged = $data['item_quantity_pledged'];
                            $pledged_item->item_quantity_complete = 0;
                            $pledged_item->pledged_item_id = $record->id;
                            $pledged_item->payment_status = 'unpaid';
                            $pledged_item->user_id = auth()->user()->id;
                            $pledged_item->save();

                            Notification::make()
                            ->title('Pledge Submitted Successfully.')
                            ->body('We thank you for your contribution. God Bless You.')
                            ->success()
                            ->send();
                        })
                        ->visible(fn() => $this->activeTab == 0 ? auth()->user()->checkPermissionTo('create BeneficiaryRequestItemPledge') : false),

                    Action::make('add_amount_payment')
                        ->fillForm(fn(BeneficiaryRequestItemPledge $record): array => [
                            'amount_payed' => $record->amount_completed > $record->amount_pledged ? 0 :$record->amount_pledged - $record->amount_completed,
                        ])
                        ->form([
                            FormGrid::make(3)
                                ->schema([
                                    DatePicker::make('pay_date')
                                        ->default(now())
                                        ->minDate(fn(Model $record) => Carbon::parse($record->created_at)->subday(1))
                                        ->required(),
                                    TextInput::make('amount_payed')
                                        ->numeric()
                                        ->helperText('Due Amount is default amount.')
                                        ->required(),
                                    Select::make('payment_mode')
                                        ->reactive()
                                        ->options(function(){
                                            $payment_modes = []; $modes = []; $payment_donation_modes = [];
                                            foreach($this->donationDetails[0] as $key => $detail){
                                                $payment_modes[] = $detail['payment_mode'];
                                            }
                                            foreach($payment_modes as $key => $mode){
                                                $modes[$key] = [
                                                    $mode => $mode
                                                ];
                                                if(is_array($modes[$key])){
                                                    $payment_donation_modes = array_merge($payment_donation_modes, $modes[$key]);
                                                }
                                            }
                                            
                                            return $payment_donation_modes;
                                        })
                                        ->required(),
                                ]),                           
                            FormSection::make('Bank Transaction Details')
                                ->description('Please verify details for ease of verification.')
                                ->schema([
                                    FormGrid::make(2)
                                        ->schema([
                                            Select::make('account_provider')
                                                ->options(function(){
                                                    $account_providers = [];
                                                    $providers = [];
                                                    $account_donation_provider = [];
                                                    foreach($this->donationDetails[0] as $detail){
                                                        if($detail['payment_mode'] == 'Bank'){
                                                            $account_providers[] = $detail['account_provider'];
                                                        }
                                                    }
                                                    if(count($account_providers) > 0){
                                                        foreach($account_providers as $key => $provider){
                                                            $providers[$key] = [
                                                                $provider => $provider
                                                            ];
                                                            if(is_array($providers[$key])){
                                                                $account_donation_provider = array_merge($account_donation_provider, $providers[$key]);
                                                            }
                                                        }
                                                    }
                                                    return $account_donation_provider;
                                                })
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

                            FormSection::make('Mobile Transaction Details')
                                ->description('Please verify details for ease of verification.')
                                ->schema([
                                    FormGrid::make(2)
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
                                ->disk('beneficiaryPledgeReceipts')
                                ->visible(function(Get $get){
                                    if(blank($get('payment_mode'))){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->required(),
                        ])
                        ->action(function(array $data, BeneficiaryRequestItemPledge $record){
                            $beneficiary_request_amount_pay = new BeneficiaryRequestItemPayment;
                            $beneficiary_request_amount_pay->payment_mode = $data['payment_mode'];
                            $beneficiary_request_amount_pay->pay_date = $data['pay_date'];
                            $beneficiary_request_amount_pay->request_type = 'Amount';
                            $beneficiary_request_amount_pay->amount_payed = $data['amount_payed'];
                            $beneficiary_request_amount_pay->account_provider = $data['payment_mode'] == 'Bank' ? $data['account_provider'] : Null;
                            $beneficiary_request_amount_pay->bank_branch_name = $data['payment_mode'] == 'Bank' ? $data['bank_branch_name'] : Null;
                            $beneficiary_request_amount_pay->bank_transaction_id = $data['payment_mode'] == 'Bank' ? $data['bank_transaction_id'] : Null;
                            $beneficiary_request_amount_pay->mobile_account_provider = $data['payment_mode'] == 'Mobile' ? $data['mobile_account_provider'] : Null;
                            $beneficiary_request_amount_pay->mobile_transaction_id = $data['payment_mode'] == 'Mobile' ? $data['mobile_transaction_id'] : Null;
                            $beneficiary_request_amount_pay->receipt_picture = $data['receipt_picture'] ?? Null;
                            $beneficiary_request_amount_pay->item_id = $record->id;
                            $beneficiary_request_amount_pay->verification_status = 'unverified';
                            $beneficiary_request_amount_pay->user_id = auth()->user()->id;
                            $beneficiary_request_amount_pay->save();

                            Notification::make()
                            ->title('Payment submitted successfully')
                            ->success()
                            ->body('Payment submitted successfully for verification.')
                            ->send();
                        })
                        ->visible(fn() => $this->activeTab == 2 ? ($record->amount_completed >= $record->amount_pledged ? false : auth()->user()->checkPermissionTo('create BeneficiaryRequestItemPayment')) : false),

                        Action::make('add_item_payment')
                            ->fillForm(fn(BeneficiaryRequestItemPledge $record): array => [
                                'item_quantity_payed' => $record->item_quantity_complete > $record->item_quantity_pledged ? 0 : $record->item_quantity_pledged - $record->item_quantity_complete,
                            ])
                            ->form([
                                FormGrid::make(2)
                                    ->schema([
                                        DatePicker::make('pay_date')
                                            ->default(now())
                                            ->minDate(fn(Model $record) => Carbon::parse($record->created_at)->subday(1))
                                            ->required(),
                                        TextInput::make('item_quantity_payed')
                                            ->numeric()
                                            ->helperText('Due Items is default item count.')
                                            ->required(),
                                    ]),
                                Checkbox::make('cash_equivalent')
                                    ->reactive()
                                    ->default(false),
                                TextInput::make('secret_key')
                                    ->unique(table: BeneficiaryRequestItemPayment::class)
                                    ->helperText('Please mark items using this key for ease verification.')
                                    ->visible(fn(Get $get) => $get('cash_equivalent') == false ? true : false),
                                Select::make('payment_mode')
                                    ->reactive()
                                    ->options(function(){
                                        $payment_modes = []; $modes = []; $payment_donation_modes = [];
                                        foreach($this->donationDetails[0] as $key => $detail){
                                            $payment_modes[] = $detail['payment_mode'];
                                        }
                                        foreach($payment_modes as $key => $mode){
                                            $modes[$key] = [
                                                $mode => $mode
                                            ];
                                            if(is_array($modes[$key])){
                                                $payment_donation_modes = array_merge($payment_donation_modes, $modes[$key]);
                                            }
                                        }
                                        
                                        return $payment_donation_modes;
                                    })
                                    ->visible(fn(Get $get) => $get('cash_equivalent') == true ? true : false)
                                    ->required(),
                                
                                FormSection::make('Bank Transaction Details')
                                    ->description('Please verify details for ease of verification.')
                                    ->schema([
                                        FormGrid::make(2)
                                            ->schema([
                                                Select::make('account_provider')
                                                    ->options(function(){
                                                        $account_providers = [];
                                                        $providers = [];
                                                        $account_donation_provider = [];
                                                        foreach($this->donationDetails[0] as $detail){
                                                            if($detail['payment_mode'] == 'Bank'){
                                                                $account_providers[] = $detail['account_provider'];
                                                            }
                                                        }
                                                        if(count($account_providers) > 0){
                                                            foreach($account_providers as $key => $provider){
                                                                $providers[$key] = [
                                                                    $provider => $provider
                                                                ];
                                                                if(is_array($providers[$key])){
                                                                    $account_donation_provider = array_merge($account_donation_provider, $providers[$key]);
                                                                }
                                                            }
                                                        }
                                                        return $account_donation_provider;
                                                    })
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

                                FormSection::make('Mobile Transaction Details')
                                    ->description('Please verify details for ease of verification.')
                                    ->schema([
                                        FormGrid::make(2)
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
                                    ->label(fn(Get $get) => $get('cash_equivalent') == true ? 'Receipt Picture' : 'Upload Item Images')
                                    ->multiple(fn(Get $get) => $get('cash_equivalent') == true ? false : true)
                                    ->downloadable()
                                    ->previewable()
                                    ->openable()
                                    ->columnSpan('full')
                                    ->disk('beneficiaryPledgeReceipts')
                                    ->visible(function(Get $get){
                                        if(blank($get('payment_mode')) && $get('cash_equivalent') == true){
                                            return false;
                                        }else{
                                            if((! blank($get('secret_key'))) || (! blank('payment_mode'))){
                                                return true;
                                            }
                                        }
                                    })
                                    ->required(),
                            ])
                            ->action(function(array $data, BeneficiaryRequestItemPledge $record){
                                $beneficiary_request_amount_pay = new BeneficiaryRequestItemPayment;
                                $beneficiary_request_amount_pay->payment_mode = $data['payment_mode'] ?? Null;
                                $beneficiary_request_amount_pay->pay_date = $data['pay_date'];
                                $beneficiary_request_amount_pay->request_type = 'Item'; 
                                $beneficiary_request_amount_pay->item_quantity_payed = $data['item_quantity_payed'];
                                $beneficiary_request_amount_pay->secret_key = $data['cash_equivalent'] == false ? $data['secret_key'] : Null;
                                $beneficiary_request_amount_pay->account_provider = array_key_exists('payment_mode', $data) ? ($data['payment_mode'] == 'Bank' ? $data['account_provider'] : Null) : Null;
                                $beneficiary_request_amount_pay->bank_branch_name = array_key_exists('payment_mode', $data) ? ($data['payment_mode'] == 'Bank' ? $data['bank_branch_name'] : Null) : Null;
                                $beneficiary_request_amount_pay->bank_transaction_id = array_key_exists('payment_mode', $data) ? ($data['payment_mode'] == 'Bank' ? $data['bank_transaction_id'] : Null) : Null;
                                $beneficiary_request_amount_pay->mobile_account_provider = array_key_exists('payment_mode', $data) ? ($data['payment_mode'] == 'Mobile' ? $data['mobile_account_provider'] : Null) : Null;
                                $beneficiary_request_amount_pay->mobile_transaction_id = array_key_exists('payment_mode', $data) ? ($data['payment_mode'] == 'Mobile' ? $data['mobile_transaction_id'] : Null) : Null;
                                $beneficiary_request_amount_pay->receipt_picture = $data['receipt_picture'] ?? Null;
                                $beneficiary_request_amount_pay->item_id = $record->id;
                                $beneficiary_request_amount_pay->verification_status = 'unverified';
                                $beneficiary_request_amount_pay->user_id = auth()->user()->id;
                                $beneficiary_request_amount_pay->save();

                                Notification::make()
                                ->title('Payment submitted successfully')
                                ->success()
                                ->body('Payment submitted successfully for verification.')
                                ->send();
                            })
                            ->visible(fn() => $this->activeTab == 1 ? ($record->item_quantity_complete >= $record->item_quantity_pledged ? false: auth()->user()->checkPermissionTo('create BeneficiaryRequestItemPayment')) : false),
                 ]);
    }

}
