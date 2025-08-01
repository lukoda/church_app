<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\BeneficiaryRequest as BeneficiaryDetails;
use App\Models\BeneficiaryRequestItempayment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use App\Models\Beneficiary;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class BeneficiaryRequest extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.beneficiary-request';

    protected static ?string $navigationGroup = 'Donation Requests';

    public int $activeTab = 0;

    public int $pledged_beneficiary_requests = 0;

    public int $pending_beneficiary_requests = 0;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Member') && Auth::guard('web')->checkPermissionTo('view BeneficiaryRequest')){
            return true;
        }else{
            return false;
        }
    }

    
    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            if(Auth::guard('web')->user()->churchMember){
                abort_unless(static::canAccess(), 403);
            }else{
                Notification::make()
                ->title('Not Registered as church member')
                ->body('Please complete or register to a church to view jumuiya offerings.')
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

    public function setPendingRequests()
    {
        $pending_requests = BeneficiaryDetails::where('church_id', auth()->user()->church_id)->where('status', 'active')->where('begin_date','<=',now())->where('end_date','>', now())->doesntHave('beneficiary_request_items.beneficiary_request_item_pledges')->doesntHave('request_amount_pledges')->count();

        $this->pending_beneficiary_requests = $pending_requests;
    }

    public function setPledgedRequests()
    {
        $pledged_requests = BeneficiaryDetails::where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->with('beneficiary_request_items')->whereHas('beneficiary_request_items.beneficiary_request_item_pledges', function(Builder $query){
            $query->where('user_id', auth()->user()->id)->whereIn('payment_status', ['partial paid', 'unpaid']);
        })
        ->orWhereHas('request_amount_pledges', function(Builder $query){
            $query->where('user_id', auth()->user()->id)->whereIn('payment_status', ['partial paid', 'unpaid']);
        })
        ->count();

        $this->pledged_beneficiary_requests = $pledged_requests;
    }

    public function getTabs(): array
    {
        return [
            'Pending Beneficiary Requests',
            'Pledged Beneficiary Requests'
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->checkPermissionTo('view BeneficiaryRequest') && auth()->user()->hasRole('Church Member');
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Beneficiary Requests')
                ->query(function(){
                    if($this->activeTab == 0){
                        return BeneficiaryDetails::query()->where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->where('end_date','>', now())->doesntHave('beneficiary_request_items.beneficiary_request_item_pledges')->doesntHave('request_amount_pledges')->where('status', 'active')->orderBy('created_at','desc');
                    }else if($this->activeTab == 1){
                        return BeneficiaryDetails::query()->where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->whereHas('beneficiary_request_items.beneficiary_request_item_pledges', function(Builder $query){
                            $query->where('user_id', auth()->user()->id)->whereIn('payment_status', ['partial paid', 'unpaid']);
                        })
                        ->orWhereHas('request_amount_pledges', function(Builder $query){
                            $query->where('user_id', auth()->user()->id)->whereIn('payment_status', ['partial paid', 'unpaid']);
                        })
                        ->orderBy('created_at','desc');
                        // ->orwhereHas('request_amount_pledges', function (Builder $query){
                        //     $query->where('user_id', auth()->user()->id);
                        // })->orwhereHas('beneficiary_request_items.beneficiary_request_item_pledges.pledge_payments', function(Builder $query){
                        //     $query->where('verification_status', 'unverified');
                        // })->orwhereHas('request_amount_pledges.pledge_payments', function(Builder $query){
                        //     $query->where('verification_status', 'unverified');
                        // })->orderBy('created_at','desc');
                    }
                    // $items_pledged = BeneficiaryDetails::where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->with('beneficiary_request_items')->whereHas('beneficiary_request_items.beneficiary_request_item_payments', function(Builder $query){
                    //     $query->where('status', 'unverified');
                    // })->count();
                    // if($items_pledged > 0){
                    //     return BeneficiaryDetails::query()->where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->with('beneficiary_request_items')->whereHas('beneficiary_request_items.beneficiary_request_item_payments', function(Builder $query){
                    //         $query->where('status', 'unverified');
                    //     })->orderBy('created_at','desc');
                    // }else{
                    //     return BeneficiaryDetails::query()->where('church_id', auth()->user()->church_id)->where('status', 'Active')->where('begin_date','<=',now())->where('end_date','>', now())->orderBy('created_at','desc');
                    // }
                })
                ->columns([
                    TextColumn::make('end_date')
                        ->date(),
                    TextColumn::make('beneficiary_type'),
                    TextColumn::make('beneficiary_id')
                        ->label('Beneficiary Name')
                        ->formatStateUsing(fn($state) => Beneficiary::whereId($state)->pluck('name')[0]),
                    TextColumn::make('title'),
                    TextColumn::make('purpose')
                        ->limit(50)
                        ->wrap(),
                    // Textcolumn::make('status')
                    //     ->badge()
                    //     ->color(fn(string $state): string => match ($state){
                    //         'Active' => 'success',
                    //         'Inactive' => 'danger',
                    //     }),
                ])
                ->emptyStateIcon('heroicon-o-document-text')
                ->emptyStateHeading('No Beneficiary Requests Registered')
                ->emptyStateDescription('Once benficiary requests registered by church will appear here.')
                ->actions([
                    Action::make('view_request')
                        ->url(function(Model $record){
                           return route('filament.admin.pages.view-beneficiary-details', ['record' => $record->id]);
                        }),
                ]);
    }

}
