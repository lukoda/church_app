<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\BeneficiaryRequest;
use App\Models\Beneficiary;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Session;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class ViewBenefeciaryRequestPayments extends Page implements HasTable
{
    use InteractsWithTable; 

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.view-benefeciary-request-payments';

    protected static ?string $navigationGroup = 'Donation Requests';

    public int $activeTab = 0;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Beneficiary') && Auth::guard('web')->user()->checkPermissionTo('view BeneficiaryRequest')){
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
            ->body('Please contact your Administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->hasRole('Beneficiary') && Auth::guard('web')->user()->checkPermissionTo('view BeneficiaryRequest');
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Beneficiary Requests')
                ->query(BeneficiaryRequest::query()->whereHas('beneficiary', function(Builder $query){
                    $query->where('phone_no', auth()->user()->phone);
                })->orderBy('created_at', 'desc'))
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
                    TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'danger'
                    })
                ])
                ->emptyStateIcon('heroicon-o-document-text')
                ->emptyStateHeading('No Beneficiary Requests Registered')
                ->emptyStateDescription('Once benficiary requests registered by church will appear here.')
                ->actions([
                        Action::make('view_payments')
                        ->url(function(Model $record){
                            return route('filament.admin.pages.view-request-payments', ['record' => $record->id]);
                         }),
                ]);
    }

}
