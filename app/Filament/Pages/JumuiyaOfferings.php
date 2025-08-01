<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\JumuiyaRevenue;
use App\Models\ChurchMember;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Filament\Tables\Actions\EditAction;
use Illuminate\Support\Facades\Auth;

class JumuiyaOfferings extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-money-bill-transfer';

    protected static string $view = 'filament.pages.jumuiya-offerings';

    protected static ?string $navigationGroup = 'Jumuiya Details';

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Jumuiya Accountant') && Auth::guard('web')->user()->checkPermissionTo('view JumuiyaRevenue')){
            return true;
        }else{
            return false;
        }
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            if(Auth::guard('web')->user()->churchMember){
                if(Auth::guard('web')->user()->churchMember->where('status', 'active')->whereNotNull('jumuiya_id')->count() > 0){
                    abort_unless(static::canAccess(), 403);
                }else{
                    Notification::make()
                    ->title('Not Registered to any Jumuiya')
                    ->body('Please register to a jumuiya to view members.')
                    ->danger()
                    ->send();
                    redirect()->to('/admin');
                }
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->checkPermissionTo('view JumuiyaRevenue');
    }

    public function table (Table $table): Table
    {
        return $table
                ->heading('Logged Jumuiya Offerings')
                ->query(function(){
                    if(auth()->user()->has('churchMember')){
                        return JumuiyaRevenue::query()->whereId(0);
                    }else{
                        return JumuiyaRevenue::query()->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('approval_status', 'Verified')->orderBy('date_recorded', 'desc');

                    }
                })
                ->columns([
                    TextColumn::make('date_recorded')
                        ->searchable(isIndividual: true)
                        ->date(),
                    TextColumn::make('amount')
                        ->numeric(
                            decimalPlaces: 0,
                            decimalSeparator: '.',
                            thousandsSeparator: ',',
                        ),
                    TextColumn::make('jumuiya_attendance')
                        ->numeric(),
                    TextColumn::make('jumuiya_host_id')
                        ->formatStateUsing(fn ($state) => ChurchMember::whereId($state)->pluck('full_name')[0])
                        ->wrap(),
                    TextColumn::make('approval_status')
                        ->formatStateUsing(function ($state){
                            if($state == Null){
                                return "Pending";
                            }
                        })
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No registered jumuiya offerings')
                ->emptyStateDescription('Once jumuiya revenues are registered will appear here')
                ->actions([
                    
                ])
                ->headerActions([
                    CreateAction::make()
                        ->form([
                            Grid::make(4)
                                ->schema([
                                    Datepicker::make('date_recorded')
                                        ->default(now())
                                        ->disabled(),
                                    TextInput::make('amount')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('jumuiya_attendance')
                                        ->numeric()
                                        ->required(),
                                    Select::make('jumuiya_host_id')
                                        ->label('Jumuiya Host Member')
                                        ->searchable()
                                        ->getSearchResultsUsing(fn(string $search) : array => ChurchMember::where('full_name', 'like', "%${search}%")->orWhere('card_no', 'like', "${search}")->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->pluck('full_name', 'id')->toArray())
                                        ->getOptionLabelUsing(fn ($value) : ?string => ChurchMember::find($value)?->full_name)
                                        ->required()
                                    ])
                        ])
                        ->action(function(array $data){
                            if(Carbon::now()->englishDayOfWeek == 'Saturday'){
                                if(JumuiyaRevenue::whereDate('date_recorded', now())->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->exists()){
                                    Notification::make()
                                    ->title('Jumuiya offering has already been submitted')
                                    ->body('Please, review jumuiya offering logs to view submitted offerings.')
                                    ->warning()
                                    ->send();
                                }else{
                                    $jumuiya_revenue = new JumuiyaRevenue;
                                    $jumuiya_revenue->jumuiya_id = auth()->user()->churchMember->jumuiya_id;
                                    $jumuiya_revenue->amount = $data['amount'];
                                    $jumuiya_revenue->date_recorded = now();
                                    $jumuiya_revenue->jumuiya_attendance = $data['jumuiya_attendance'];
                                    $jumuiya_revenue->jumuiya_host_id = $data['jumuiya_host_id'];
                                    $jumuiya_revenue->approval_status = 'Unverified';
                                    $jumuiya_revenue->save();
    
                                    Notification::make()
                                    ->title('Jumuiya offering has been submitted successfully')
                                    ->body('Jumuiya Offering logged on '. now()->toDateString().' sent successfully to church.')
                                    ->success()
                                    ->send();
                                }
                            }else{
                                Notification::make()
                                ->title('Jumuiya offering can only be submitted on satuday  every week of jumuiya mass')
                                ->body('Please, log jumuiya offerings on the day of jumuiya mass.')
                                ->warning()
                                ->send();
                            }
                        })
                        ->visible(fn() => Carbon::now()->englishDayOfWeek == 'Saturday' && auth()->user()->checkPermissionTo('create JumuiyaRevenue') ? true : false)
                    ]);
    }

}
