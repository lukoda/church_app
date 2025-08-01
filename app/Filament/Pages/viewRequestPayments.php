<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\BeneficiaryRequestItemPledge;
use App\Models\BeneficiaryRequestItemPayment;
use App\Models\BeneficiaryRequest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Columns\Summarizers\Summarizer;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use App\Models\ChurchMember;
use App\Models\Church;
use Filament\Forms\Components\Grid;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class viewRequestPayments extends Page implements HasTable
{
    use InteractsWithtable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-request-payments';

    public int $activeTab = 0;

    public int $record = 0;

    private int $passed_record_param = 0;

    public int $unverifiedPledges = 0;

    public int $verifiedPledges = 0;

    // public int $allPledges = 0;

    public int $verifiedAmountPledges = 0;

    public int $unVerifiedAmountPledges = 0;

    public int $currentItemPayment = 0;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Beneficiary')){
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
            redirect()->to('/admin/view-benefeciary-request-payments'); 
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
        if(static::canAccess()){
            if(BeneficiaryRequest::whereId($this->passed_record_param)->whereHas('beneficiary', function(Builder $query){
                $query->where('phone_no', auth()->user()->phone);
            })->exists()){
                abort_unless(static::canAccess(), 403);
            }else{
                Notification::make()
                ->title('Page Not Found')
                ->body('Sorry, the requested page does not exist.')
                ->danger()
                ->send();
                redirect()->to('/admin/view-benefeciary-request-payments');
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

    public function setUnverifiedPledges()
    {
        $this->unverifiedPledges = BeneficiaryRequestItemPayment::whereRelation('beneficiary_request_item.beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request_item')
                                        ->whereIn('verification_status', ['unverified', 'unpaid'])->count();
    }

    public function setVerifiedPledges()
    {
        $this->verifiedPledges = BeneficiaryRequestItemPayment::whereRelation('beneficiary_request_item.beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request_item')
                                        ->whereIn('verification_status', ['verified', 'partial paid'])->count();
    }

    public function setVerifiedAmountPledges()
    {
        $this->verifiedAmountPledges = BeneficiaryRequestItemPayment::whereRelation('beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request')
                                        ->whereIn('verification_status', ['verified', 'partial paid'])->count();
    }

    public function setUnVerifiedAmountPledges()
    {
        $this->unVerifiedAmountPledges = BeneficiaryRequestItemPayment::whereRelation('beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request')
                                        ->whereIn('verification_status', ['unverified', 'unpaid'])->count();
    }

    // public function setAllPledges()
    // {
    //     $this->allPledges = BeneficiaryRequestItemPledge::whereRelation('beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->orWhereRelation('beneficiary_request', 'id','=', $this->record)->count();
    // }

    public function mount()
    {
        $this->setRecord();
    }

    public function updatedActiveTab()
    {
        $this->resetTable();
    }

    public function getTabs(): array
    {
        return [
            'Verified Item Payments',
            'Unverified Item Payments',
            'Unverified Amount Payments',
            'Verified Amount Payments'
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public function setrecord($id)
    {
        if(is_numeric($id)){
            $this->record = $id;
        }else{
            Notification::make()
            ->title('Page Not Found')
            ->body('Sorry, the requested page does not exist.')
            ->danger()
            ->send();
            redirect()->to('/admin/view-benefeciary-request-payments'); 
            // redirect()->to('admin/view-benefeciary-request-payments');
            // Notification::make()
            //     ->danger()
            //     ->title('The record doesn\'t exist')
            //     ->body('Please select valid record in table.')
            //     ->send();
        }
    }

    public function loadGroups(): array
    {
        // if($this->activeTab == 0){
        //     $beneficiary_request = BeneficiaryRequest::whereId($this->record)->first();
        //     if($beneficiary_request->request_amount_pledges->count() > 0 && $beneficiary_request->beneficiary_request_items->count() < 0){
        //         return [
        //             Group::make('beneficiary_request.amount')
        //             ->label('Beneficiary Amount')
        //             ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => ucfirst( $record->beneficiary_request->title))
        //             ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => "Amount requested ".$record->beneficiary_request->amount),
        //         ];
        //     }else if($beneficiary_request->beneficiary_request_items->count() > 0 && $beneficiary_request->request_amount_pledges->count() < 0){
        //         return [
        //             Group::make('beneficiary_request_item.item')
        //             ->label('Item')
        //             ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => ucfirst( $record->beneficiary_request_item->item))
        //             ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => $record->beneficiary_request_item->description),
        //         ];
        //     }else{
        //         return [
        //             Group::make('beneficiary_request_item.item')
        //                 ->label('Item')
        //                 ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => ucfirst( $record->beneficiary_request_item->item))
        //                 ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => $record->beneficiary_request_item->description),
    
        //             Group::make('beneficiary_request.amount')
        //                 ->label('Beneficiary Amount')
        //                 ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => ucfirst( $record->beneficiary_request->title))
        //                 ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPledge $record): string => "Amount requested ".$record->beneficiary_request->amount),
        //         ];
        //     }
 
        // }else
         if($this->activeTab == 0 || $this->activeTab == 1){
            return [
                Group::make('item')
                    ->label('Item Name')
                    ->groupQueryUsing(fn (Builder $query) => $query->with('beneficiary_request_item.beneficiary_request_item')->groupBy('item'))
                    ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPayment $record): string => ucfirst( $record->beneficiary_request_item->beneficiary_request_item->item))
                    ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPayment $record): string => $record->beneficiary_request_item->beneficiary_request_item->description)
                    ->collapsible()

            ];
        }else if($this->activeTab == 2 || $this->activeTab == 3){
            return [
                Group::make('amount_requested')
                    ->label('Amount Requested')
                    ->groupQueryUsing(fn (Builder $query) => $query->with('beneficiary_request_item.beneficiary_request')->groupBy('amount'))
                    ->getTitleFromRecordUsing(fn (BeneficiaryRequestItemPayment $record): string => number_format($record->beneficiary_request_item->beneficiary_request->amount))
                    ->getDescriptionFromRecordUsing(fn (BeneficiaryRequestItemPayment $record): string => "Amount requested by Beneficiary : ".number_format($record->beneficiary_request_item->beneficiary_request->amount))
                    ->collapsible()
            ];
        }
    }

    public function setDefaultGroup(): string
    {
        // if($this->activeTab == 0){
        //     $beneficiary_request = BeneficiaryRequest::whereId($this->record)->first();
        //     if($beneficiary_request->request_amount_pledges->count() > 0 && $beneficiary_request->beneficiary_request_items->count() < 0){
        //         return 'beneficiary_request_item.beneficiary_request.amount';
        //     }else if($beneficiary_request->beneficiary_request_items->count() > 0 && $beneficiary_request->request_amount_pledges->count() < 0){
        //         return 'beneficiary_request_item.beneficiary_request_item.item';
        //     }else{
        //         return 'beneficiary_request_item.beneficiary_request.amount';
        //     }
        // }else 
        if($this->activeTab == 0 || $this->activeTab == 1){
            return 'beneficiary_request_item.beneficiary_request_item.item';
        }else if($this->activeTab == 2 || $this->activeTab == 3){
            return 'beneficiary_request_item.beneficiary_request.amount';
        }
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Item Request Payments')
                ->groups($this->loadGroups())
                ->defaultGroup($this->setDefaultGroup())
                ->groupsOnly(function(){
                    return false;
                })
                ->query(function(){
                    // if($this->activeTab == 0){
                    //     return BeneficiaryRequestItemPledge::query()->whereRelation('beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->orWhererelation('beneficiary_request', 'id', '=', $this->record)->with('pledge_payments')->orderBy('created_at', 'desc');
                    // }else 
                    if($this->activeTab == 0){
                        return BeneficiaryRequestItemPayment::query()->whereRelation('beneficiary_request_item.beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request_item')
                                ->whereIn('verification_status', ['verified', 'partial paid'])->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 1){
                        return BeneficiaryRequestItemPayment::query()->whereRelation('beneficiary_request_item.beneficiary_request_item.beneficiary_request', 'id','=', $this->record)->with('beneficiary_request_item')
                                ->whereIn('verification_status', ['unverified', 'unpaid'])->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 2){
                        return BeneficiaryRequestItemPayment::query()->whereRelation('beneficiary_request_item.beneficiary_request', 'id', '=', $this->record)->with('beneficiary_request_item')
                                ->whereIn('verification_status', ['unverified','unpaid'])->orderBy('created_at', 'desc');
                    }else if($this->activeTab == 3){
                        return BeneficiaryRequestItemPayment::query()->whereRelation('beneficiary_request_item.beneficiary_request', 'id', '=', $this->record)->with('beneficiary_request_item')
                                ->whereIn('verification_status', ['verified', 'partial paid'])->orderBy('created_at', 'desc');
                    }
                })->columns([
                        // TextColumn::make('item_name')
                        //     ->default(fn(Model $record) => $record->beneficiary_request_item->item)
                        //     ->description(fn(Model $record) => $record->beneficiary_request_item->description)
                        //     ->visible(fn() => $this->activeTab == 0 ? true : false),
                        // TextColumn::make('name')
                        //     ->label('item_name')
                        //     ->default(fn(Model $record) => $record->beneficiary_request_item->beneficiary_request_item->item)
                        //     ->description(fn(Model $record) => $record->beneficiary_request_item->beneficiary_request_item->description)
                        //     ->visible(fn() => $this->activeTab == 1 || $this->activeTab == 2 ? true : false),
                        TextColumn::make('church')
                            ->label('Church')
                            ->default(fn(Model $record) => Church::whereId(ChurchMember::where('user_id', $record->user_id)->pluck('church_id')[0])->pluck('name')[0])
                            ->visible(fn() => $this->activeTab == 0 || $this->activeTab == 1 || $this->activeTab == 2 || $this->activeTab == 3  ? true : false),
                        TextColumn::make('user_id')
                            ->label('Pledged By')
                            ->formatStateUsing(fn($state) => ChurchMember::where('user_id', $state)->pluck('surname')[0])
                            ->visible(fn() => $this->activeTab == 0 || $this->activeTab == 1 || $this->activeTab == 2 || $this->activeTab == 3  ? true : false),
                        TextColumn::make('phone_no')
                            ->label('Phone No')
                            ->default(fn(Model $record) => substr_replace(ChurchMember::where('user_id', $record->user_id)->pluck('phone')[0], '*', -1))
                            ->visible(fn() => $this->activeTab == 0 || $this->activeTab == 1 || $this->activeTab == 2 || $this->activeTab == 3  ? true : false),
                        // TextColumn::make('verfied_pledged_items')
                        //     ->default(fn(Model $record) => $record->pledge_payments->where('verification_status', 'verified')->sum('item_quantity_payed'))
                        //     ->numeric()
                        //     ->hidden(fn() => $this->activeTab == 0 ? false : true)
                        //     ->summarize(
                        //         Summarizer::make()
                        //             ->label('Total Verified Pledges')
                        //             ->using(fn (QueryBuilder $query): string => $query->join('beneficiary_request_item_payments', 'beneficiary_request_item_pledges.id', '=', 'beneficiary_request_item_payments.item_id')->where('verification_status', 'verified')->sum('item_quantity_payed'))
                        //         ),
                        // TextColumn::make('unverified_pledged_items')
                        //     ->default(fn(Model $record) => $record->pledge_payments->where('verification_status', 'unverified')->sum('item_quantity_payed'))
                        //     ->numeric()
                        //     ->hidden(fn() => $this->activeTab == 0 ? false : true)
                        //     ->summarize(
                        //         Summarizer::make()
                        //             ->label('Total Unverified Pledges')
                        //             ->using(fn (QueryBuilder $query): string => $query->join('beneficiary_request_item_payments', 'beneficiary_request_item_pledges.id', '=', 'beneficiary_request_item_payments.item_id')->where('verification_status', 'unverified')->sum('item_quantity_payed'))
                        //     ),
                        TextColumn::make('beneficiary_request_item.item_quantity_pledged')
                            ->label('item_quantity_pledged')
                            ->numeric()
                            ->visible(fn() => $this->activeTab == 0 || $this->activeTab == 1 ? true : false),
                        TextColumn::make('beneficiary_request_item.amount_pledged')
                            ->label('Amount Pledged')
                            ->numeric()
                            ->visible(fn() => $this->activeTab == 2 || $this->activeTab == 3 ? true : false),
                        // TextColumn::make('item_quantity_pledged')
                        //     ->label('item_quantity_pledged')
                        //     ->numeric()
                        //     ->hidden(fn() => $this->activeTab == 0 ? false : true)
                        //     ->summarize(
                        //         Sum::make()
                        //             ->label('Total Pledged Items')
                        //     ),
                        // TextColumn::make('item_quantity_complete')
                        //     ->label('item_quantity_complete')
                        //     ->numeric()
                        //     ->hidden(fn() => $this->activeTab == 0 ? false : true)
                        //     ->summarize(
                        //         Sum::make()
                        //             ->label('Total Payed Items')
                        //     ),
                        TextColumn::make('item_quantity_payed')
                            ->label('item_quantity_payed')
                            ->numeric()
                            ->visible(fn() =>$this->activeTab == 0 || $this->activeTab == 1 ? true : false)
                            ->description(fn(Model $record) => "Due items ".number_format($record->item_quantity_payed - $record->item_quantity_verified)),
                        TextColumn::make('amount_payed')
                            ->label('Amount Payed')
                            ->numeric()
                            ->visible(fn() =>$this->activeTab == 2 || $this->activeTab == 3 ? true : false)
                            ->description(fn(Model $record) => "Due balance is ".number_format($record->amount_payed - $record->amount_payed_verified)),
                        TextColumn::make('amount_payed_verified')
                            ->label('Verified Amount')
                            ->numeric()
                            ->visible(fn() =>$this->activeTab == 2 || $this->activeTab == 3 ? true : false),
                        TextColumn::make('verification_status')
                            ->badge()
                            ->color(fn(string $state): string => match ($state){
                                'verified' => 'success',
                                'partial paid' => 'warning',
                                'unpaid' => 'secondary',
                                'unverified' => 'danger'
                            })
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No registered jumuiya offerings')
                ->emptyStateDescription('Once jumuiya revenues are registered will appear here')
                ->actions([
                        Action::make('verify_payment')
                            ->fillForm(fn(Model $record) : array => [
                                'picture' => $record->receipt_picture,
                                'item_quantity_payed' => $record->item_quantity_payed,
                            ])
                            ->form([
                                Grid::make(2)
                                ->schema([
                                    TextInput::make('item_quantity_payed')
                                    ->disabled(),
                                    TextInput::make('item_quantity_verified')
                                    ->default(0)
                                    ->minValue(0)
                                    ->numeric()
                                    ->required(),
                                ]),
                                FileUpload::make('picture')
                                ->downloadable()
                                ->previewable()
                                ->openable()
                                ->columnSpan('full')
                                ->disk('beneficiaryPledgeReceipts')
                                ->disabled(),
                            ])
                            ->action(function(array $data, Model $record){
                                if($data['item_quantity_verified'] >= $record->item_quantity_payed){
                                    $record->update([
                                        'item_quantity_verified' => $data['item_quantity_erified'],
                                        'verification_status' => 'verified'
                                    ]);

                                    $record->beneficiary_request_item->update([
                                        'item_quantity_complete' => $record->beneficiary_request_item->item_quantity_complete + $data['item_quantity_verified'],
                                    ]);

                                    if($record->beneficiary_request_item->item_quantity_complete >= $record->beneficiary_request_item->item_quantity_pledged){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'paid'
                                        ]);
                                   }else if($record->beneficiary_request_item->item_quantity_complete > 0){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'partial paid'
                                        ]);
                                   }


                                }else{
                                    if($data['item_quantity_verified'] > 0){
                                        $record->update([
                                            'item_quantity_verified' => $data['item_quantity_verified'],
                                            'verification_status' => 'partial paid'
                                        ]);

                                    $record->beneficiary_request_item->update([
                                        'item_quantity_complete' => $record->beneficiary_request_item->item_quantity_complete + $data['item_quantity_verified'],
                                    ]);

                                    if($record->beneficiary_request_item->item_quantity_complete >= $record->beneficiary_request_item->item_quantity_pledged){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'paid'
                                        ]);
                                   }else if($record->beneficiary_request_item->item_quantity_complete > 0){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'partial paid'
                                        ]);
                                   }
                                }else{
                                        $record->update([
                                            'item_quantity_verified' => $data['item_quantity_verified'],
                                            'verification_status' => 'unpaid'
                                        ]);
                                    }
                                }
                            })
                            ->visible(fn() => $this->activeTab == 1 ? auth()->user()->checkPermissionTo('verify BeneficiaryRequestPayments') : false),

                        Action::make('verify_payment')
                            ->fillForm(fn(Model $record) : array => [
                                'picture' => $record->receipt_picture,
                                'amount_payed' => $record->amount_payed,
                            ])
                            ->form([
                                Grid::make(2)
                                ->schema([
                                    TextInput::make('amount_payed')
                                    ->disabled(),
                                    TextInput::make('amount_verified')
                                    ->default(0)
                                    ->minValue(0)
                                    ->numeric()
                                    ->required(),
                                ]),
                                FileUpload::make('picture')
                                ->downloadable()
                                ->previewable()
                                ->openable()
                                ->columnSpan('full')
                                ->disk('beneficiaryPledgeReceipts')
                                ->disabled(),
                            ])
                            ->action(function(array $data, Model $record){
                                if($data['amount_verified'] >= $record->amount_payed){
                                    $record->update([
                                        'amount_payed_verified' => $data['amount_verified'],
                                        'verification_status' => 'verified'
                                    ]);

                                    $record->beneficiary_request_item->update([
                                        'amount_completed' => $record->beneficiary_request_item->amount_completed + $data['amount_verified']
                                    ]);

                                    if($record->beneficiary_request_item->amount_completed >= $record->beneficiary_request_item->amount_pledged){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'paid'
                                        ]);
                                   }else if($record->beneficiary_request_item->amount_completed > 0){
                                        $record->beneficiary_request_item->update([
                                            'payment_status' => 'partial paid'
                                        ]);
                                   }
                                }else {
                                    if($data['amount_verified'] > 0){
                                        $record->update([
                                            'amount_payed_verified' => $data['amount_verified'],
                                            'verification_status' => 'partial paid'
                                        ]);

                                        $record->beneficiary_request_item->update([
                                            'amount_completed' => $record->beneficiary_request_item->amount_completed + $data['amount_verified']
                                        ]);

                                        if($record->beneficiary_request_item->amount_completed >= $record->beneficiary_request_item->amount_pledged){
                                            $record->beneficiary_request_item->update([
                                                'payment_status' => 'paid'
                                            ]);
                                       }else if($record->beneficiary_request_item->amount_completed > 0){
                                            $record->beneficiary_request_item->update([
                                                'payment_status' => 'partial paid'
                                            ]);
                                       }

                                    }else{
                                        $record->update([
                                            'amount_payed_verified' => $data['amount_verified'],
                                            'verification_status' => 'unpaid'
                                        ]);

                                    }
                                }
                            })
                            ->visible(fn() => $this->activeTab == 2 ? auth()->user()->checkPermissionTo('verify BeneficiaryRequestPayments') : false),
                ]);
    }

}
