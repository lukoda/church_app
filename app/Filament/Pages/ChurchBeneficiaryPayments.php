<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\Beneficiary;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Tables\Grouping\Group;
use App\Models\ChurchMember;
use App\Models\BeneficiaryRequest;
use DB;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ChurchBeneficiaryPayments extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.church-beneficiary-payments';

    protected static ?string $navigationGroup = 'Donation Requests';

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Secretary') && Auth::guard('web')->user()->checkPermissionTo('view BeneficiaryRequest')){
            return true;
        }else{
            return false;
        }
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            abort_unless(static::canAccess(), 403);
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
        return Auth::guard('web')->user()->hasRole('Church Secretary');
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Logged Beneficiary Requests')
                ->groups([
                    Group::make('beneficiary.name')
                    ->label('Beneficiary Name')
                    ->getTitleFromRecordUsing(fn (BeneficiaryRequest $record): string => ucfirst(Beneficiary::whereId($record->beneficiary_id)->pluck('name')[0]))
                    ->getDescriptionFromRecordUsing(function (BeneficiaryRequest $record): string {
                        if($record->type == 'group'){
                            return $record->group_leader_name.', phone number is : '.$record->beneficiary->phone_no;
                        }else{
                            return 'Beneficiary phone no is  : '.$record->beneficiary->phone_no;
                        }
                    }),
                ])
                ->defaultGroup('beneficiary.name')
                ->query(BeneficiaryRequest::query()->where('church_id', auth()->user()->church_id)->where('begin_date','<=',now())->where('end_date', '>=', now())
                ->orwhereHas('beneficiary_request_items.beneficiary_request_item_pledges.pledge_payments', function(Builder $query){
                    $query->where('verification_status', 'unverified');
                })->orwhereHas('request_amount_pledges.pledge_payments', function(Builder $query){
                    $query->where('verification_status', 'unverified');
                })->with('beneficiary')->orderBy('created_at','desc'))
                ->columns([
                    TextColumn::make('end_date')
                    ->label('End Date')
                    ->date(),
                    TextColumn::make('title')
                    ->label('Title'),
                    TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn($state) => number_format($state)),
                    TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(5)
                    ->wrap(),
                    TextColumn::make('status')
                    ->label('Status'),
                    // TextColumn::make('total_pending_requests')
                    //     ->default(fn(Beneficiary $record) => $record->beneficiary_requests->count()),
                    TextColumn::make('registered_by')
                        ->label('Registered By')
                        ->wrap()
                        ->formatStateUsing(fn($state) => ChurchMember::where('user_id', $state)->pluck('surname')[0])
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No registered beneficiary requests')
                ->emptyStateDescription('Once you have registered beneficiary requests will appear here.')
                ->actions([
                    Action::make('view_requests')
                    ->url(function(Model $record){
                        return route('filament.admin.pages.view-request-payments', ['beneficiary_request' => $record->id]);
                     }),
                ]);
                
    }
}
