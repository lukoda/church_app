<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\ChurchMember;
use App\Models\Ward;
use App\Models\District;
use App\Models\Region;
use App\Models\User;
use App\Models\JumuiyaChairPerson;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Get;
use Filament\Support\Enums\MaxWidth;
use carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use App\Models\JumuiyaMember;
use DB;
use Illuminate\Support\Facades\Auth;


class JumuiyaMembers extends Page implements HasTable
{
    use InteractsWithTable; 

    protected static ?string $navigationIcon = 'fas-people-roof';

    protected static string $view = 'filament.pages.jumuiya-members';

    protected static ?string $navigationGroup = 'Jumuiya Details';

    // protected static ?string $navigationBadgeTooltip = 'The number of uverified members';

    public int $activeTab = 0;

    public int $all_members;

    public int $verified_members;

    public int $unverified_members;

    public int $notapproved_members;

    public static function canAccess(): bool
    {
        if((Auth::guard('web')->user()->hasRole('Jumuiya Chairperson') || Auth::guard('web')->user()->hasRole('Committee Member')) && Auth::guard('web')->user()->checkPermissionTo('view-any JumuiyaMember')){
            return true;
        }else {
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
                ->title('Not Registered to any Jumuiya')
                ->body('Please register to a jumuiya to view members.')
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
        return Auth::guard('web')->user()->checkPermissionTo('view-any JumuiyaMember') && (auth()->user()->hasRole('Jumuiya Chairperson') || auth()->user()->hasRole('Committee Member'));
    }

    public static function getNavigationBadge(): ?string
    {
        return Auth::guard('web')->user()->churchMember != Null ? Churchmember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereNull('status')->whereNotNUll('card_no')->count() : 0;
    }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    public static function getNavigationBadgeColor(): string | array | null
    {
        return 'danger';
    }

    public function getTabs(): array
    {
        return [
            'All',
            'Verified Members',
            'Unverified Members',
            'Not Approved Members',
        ];
    }

    public function setVerifiedMembers()
    {
        if(auth()->user()->churchMember){
            $this->verified_members = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereHas('jumuiyaMember', function(Builder $query){
                $query->where('status', 'active');
            })->whereNotNull('card_no')->count() ?? 0;
        }else{
            $this->verified_members = 0;
        }
    }

    public function setAllMembers()
    {
        if(auth()->user()->churchMember){
            $this->all_members = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereHas('jumuiyaMember', function(Builder $query){
                $query->whereIn('status', ['active', 'inactive']);
            })->whereNotNull('card_no')->count() ?? 0;
        }else{
            $this->all_members = 0;
        }
    }

    public function setUnverifiedMembers()
    {
        if(auth()->user()->churchMember){
            $this->unverified_members = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereNotNull('card_no')->whereNull('status')->count() ?? 0;
        }else{
            $this->unverified_members = 0;
        }
    }

    public function setNotApprovedMembers()
    {
        if(auth()->user()->churchMember){
            $this->notapproved_members = ChurchMember::whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'Disapproved')->count() ?? 0;
        }else{
            $this->notapproved_members = 0;
        }
    }

    public function table (Table $table): Table
    {
        return $table
                ->heading('Jumuiya Members')
                ->searchable()
                ->query(function(){
                    if($this->activeTab == 0){
                        if(auth()->user()->churchMember == Null){
                            return ChurchMember::query()->whereId(0);
                        }else{
                            return ChurchMember::query()->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereIn('status', ['active', 'inactive'])->whereNotNull('card_no')->orderBy('created_at', 'desc');
                        }
                    }else if($this->activeTab == 1){
                        if(auth()->user()->churchMember == Null){
                            return ChurchMember::query()->whereId(0);
                        }else{
                            return ChurchMember::query()->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->whereNotNull('card_no')->orderBy('created_at', 'desc');
                        }
                    }else if($this->activeTab == 2){
                        if(auth()->user()->churchMember == Null){
                            return ChurchMember::query()->whereId(0);
                        }else{
                            return ChurchMember::query()->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->whereNull('status')->whereNotNull('card_no')->orderBy('created_at', 'desc');
                        }
                    }else if($this->activeTab == 3){
                        if(auth()->user()->churchMember == Null){
                            return ChurchMember::query()->whereId(0);
                        }else{
                            return ChurchMember::query()->whereNotNull('jumuiya_id')->where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'Disapproved')->orderBy('created_at', 'desc');
                        }
                    }
                })
                ->columns([
                    TextColumn::make('full_name')
                        ->wrap()
                        ->searchable(isIndividual: true),
                    TextColumn::make('phone')
                        ->searchable(),
                    TextColumn::make('gender'),
                    TextColumn::make('date_of_request')
                        ->default(function(Model $record){
                            return Carbon::parse($record->created_at)->format('Y-m-d');
                        }),
                    // TextColumn::make('is_NewMember')
                    //     ->badge()
                    //     ->color(fn(bool $state): string => match ($state){
                    //         true => 'success',
                    //         false => 'warning'
                    //     }),
                    TextColumn::make('card_no')
                        ->searchable(isIndividual: true),
                    TextColumn::make('marital_status')
                        ->searchable(isIndividual: true),
                    // TextColumn::make('status')
                    //     ->badge()
                    //     ->color(fn(string $state): string => match ($state){
                    //         'active' => 'success',
                    //         'inactive' => 'warning'
                    //     })
                    //     ->formatStateUsing(function($state){
                    //         if($state == 'active'){
                    //             return "Verified";
                    //         }else{
                    //             return "Not Verified";
                    //         }
                    //     }),
                    TextColumn::make('comment')
                        ->default('No Comment'),

                    TextColumn::make('jumuiyaMember.status')
                        ->label('Member Jumuiya Status')
                        ->color(fn(string $state): string => match ($state){
                            'active' => 'success',
                            'inactive' => 'danger'
                        })             
                ])
                ->emptyStateIcon('fas-people-group')
                ->emptyStateHeading('No registered jumuiya members')
                ->emptyStateDescription('Once you have registered members will appear here.')
                ->actions([
                    Action::make('verify_member')
                        ->label('Verfy Member')
                        ->modalWidth(MaxWidth::SixExtraLarge)
                        ->fillForm(fn (ChurchMember $record): array => [
                            'full_name' => $record->full_name,
                            'phone' => $record->phone,
                            'gender' => $record->gender,
                            'marital_status' => $record->marital_status,
                            'date_of_birth' => $record->date_of_birth,
                            'isNewMember' => $record->isNewMember,
                            'card_no' => $record->card_no,
                            'received_confirmation' => $record->received_confirmation,
                            'received_baptism' => $record->received_baptism,
                            'profession' => $record->profession,
                            'volunteering_in' => $record->volunteering_in,
                        ])
                        ->form([
                            Grid::make(4)
                                ->schema([
                                    TextInput::make('full_name')
                                        ->disabled(),
                                    TextInput::make('phone')
                                        ->disabled(),
                                    TextInput::make('gender')
                                        ->disabled(),
                                    TextInput::make('marital_status')
                                    ->disabled(),
                                    TextInput::make('date_of_birth')
                                        ->disabled(),
                                    TextInput::make('address')
                                        ->disabled()
                                        ->default(function(Model $record){
                                            if($record->ward_id != Null){
                                                return Ward::whereId($record->ward_id)->pluck('name')[0].','.District::whereId($record->district_id)->pluck('name')[0].'.'.Region::whereId($record->region_id)->pluck('name')[0];
                                            }else {
                                                if($record->district_id != Null){
                                                    return District::whereId($record->district_id)->pluck('name')[0].'.'.Region::whereId($record->region_id)->pluck('name')[0];
                                                }else{
                                                    if($record->region_id != Null){
                                                        return Region::whereId($record->region_id)->pluck('name')[0];
                                                    }
                                                }
                                            }
                                        }),
                                    Checkbox::make('is_NewMember')
                                        ->disabled(),
                                    TextInput::make('card_no')
                                        ->disabled()
                                        ->visible(function(Model $record){
                                            if($record->card_no != Null){
                                                return $record->card_no;
                                            }else{
                                                return 'No Card No';
                                            }
                                        }),
                                    Checkbox::make('received_confirmation')
                                        ->disabled(),
                                    Checkbox::make('received_baptism')
                                        ->disabled(),
                                    TextInput::make('profession')
                                        ->disabled(),
                                    Select::make('volunteering_in')
                                        ->multiple()
                                        ->options([
                                            'fellowship' => 'Fellowship',
                                            'kwaya' => 'kwaya',
                                            'usafi' => 'usafi',
                                            'uwalimu watoto' => 'uwalimu watoto',
                                            'upambaji' => 'upambaji'
                                        ])
                                        ->disabled(),
                                    TextInput::make('comment'),
                                ]),

                            // Step::make('approval_details')
                            //     ->description('Approve/Disapprove Jumuiya Member')
                            //     ->schema([
                            //         Select::make('status')
                            //             ->options([
                            //                 'active' => 'Approve',
                            //                 'inactive' => 'Not Approved'
                            //             ])
                            //             ->reactive()
                            //             ->required(),
        
                            //         TextInput::make('comment')
                            //             ->visible(function(Get $get){
                            //                 if($get('status') == 'inactive'){
                            //                     return true;
                            //                 }else{
                            //                     return false;
                            //                 }
                            //             })
                            //             ->required(),
                            //     ])->columns(2)
                        ])
                        ->action(function(array $data, ChurchMember $record, array $arguments): void{
                            if($arguments['status'] == true){
                                $record->comment = $data['comment'] ?? Null;
                                $record->status = 'active';
                                $record->physically_approved_by = auth()->user()->id;
                                $record->date_registered = now();
                                $record->save();
    
                                $user = User::find($record->user_id);
    
                                $jumuiya_member = new JumuiyaMember;
                                $jumuiya_member->church_member_id = $record->id;
                                $jumuiya_member->jumuiya_id = $record->jumuiya_id;
                                $jumuiya_member->date_registered = now();
                                $jumuiya_member->status = 'active';
                                $jumuiya_member->save();

                                $user->removeRole('Guest');
                                $user->assignRole('Church Member');

                                Notification::make()
                                ->title('Member successfully approved')
                                ->success()
                                ->send();
                            }else if($arguments['status'] == false){
                                $record->comment = $data['comment'] ?? Null;
                                $record->status = 'Disapproved';
                                $record->physically_approved_by = auth()->user()->id;
                                $record->date_registered = now();
                                $record->save();

                                Notification::make()
                                ->title('Member successfully disapproved')
                                ->success()
                                ->send();
                            }


                        })
                        ->visible(function(Model $record){
                            if($record->status == Null){
                                if(auth()->user()->checkPermissionTo('verify ChurchMember')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }else{
                                return false;
                            }
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalFooterActions(fn (Action $action): array => [
                            $action->makeModalSubmitAction('Approve', arguments: ['status' => true])->color('success'),
                            $action->makeModalSubmitAction('Disapprove', arguments: ['status' => false])->color('danger')
                        ]),

                    Action::make('deactivate_membership')
                        ->label(function(Model $record){
                            $jumuiya_member = JumuiyaMember::where('church_member_id', $record->id)->first();
                            if($jumuiya_member->status == 'active'){
                                return 'Deactivate Member';
                            }else{
                                return 'Activate Member';
                            }
                        })
                        ->form([
                            // Select::make('status')
                            //     ->options([
                            //         'active' => 'Approve',
                            //         'inactive' => 'Not Approved'
                            //     ])
                            //     ->reactive()
                            //     ->required(),

                            TextInput::make('comment')
                                ->required(),
                        ])
                        ->action(function(array $data, ChurchMember $record, array $arguments): void{
                            if($arguments['status'] == true){
                                $jumuiya_member = JumuiyaMember::where('church_member_id', $record->id)->first();
                                $jumuiya_member->status = 'active';
                                $jumuiya_member->save();

                                $record->comment = $data['comment'] ?? Null;
                                $record->save();
                                Notification::make()
                                    ->title('Member successfully Activated')
                                    ->success()
                                    ->send();
                            }else if($arguments['status'] == false){
                                $jumuiya_member = JumuiyaMember::where('church_member_id', $record->id)->first();
                                $jumuiya_member->status = 'inactive';
                                $jumuiya_member->save();

                                $record->comment = $data['comment'] ?? Null;
                                $record->save();
                                Notification::make()
                                    ->title('Member successfully Deactivated')
                                    ->success()
                                    ->send();
                            }

                        })
                        ->visible(function(Model $record){
                            $jumuiya_member = JumuiyaMember::where('church_member_id', $record->id)->first();
                            if($jumuiya_member->status == 'active' || $jumuiya_member->status == 'inactive'){
                                if(auth()->user()->checkPermissionTo('deactivate JumuiyaMember')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }else{
                                return false;
                            }
                        })
                        ->modalSubmitAction(false)
                        ->modalCancelAction(false)
                        ->modalFooterActions(function(Action $action, Model $record){
                            $jumuiya_member = JumuiyaMember::where('church_member_id', $record->id)->first();
                            if($jumuiya_member->status == 'active'){
                                return [
                                    $action->makeModalSubmitAction('Deactivate Membership', arguments: ['status' => false])->color('danger')
                                ];
                            }else if($jumuiya_member->status == 'inactive'){
                                return [
                                    $action->makeModalSubmitAction('Activate Membership', arguments: ['status' => true])->color('success')
                                ]; 
                            }
                        }),

                    Action::make('assign_role')
                        ->form([
                            Select::make('role')
                                ->options(function(){
                                    return DB::table('roles')->where('name', 'like', '%Jumuiya Chairperson%')->orWhere('name', 'like', '%Jumuiya Accountant%')->pluck('name', 'id');
                                })
                                ->required(),
                        ])
                        ->action(function(array $data, Model $record){
                            if($data['role'] == 'Jumuiya Chairperson' && JumuiyaChairperson::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->exists()){
                                $jumuiya_chairperson = ChurchMember::whereId(JumuiyaChairperson::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->pluck('church_member_id'))->pluck('full_name');
                                Notification::make()
                                ->title($data['role'].' has already been assigned to '. $jumuiya_chairperson[0])
                                ->body('Unassign current jumuiya chairperson'. $jumuiya_chairperson[0]. 'in order to assign new Chairperson for jumuiya')
                                ->warning()
                                ->send();
                            }else if($data['role'] == 'Jumuiya Accountant' && JumuiyaAccountant::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->exists()){
                                $jumuiya_accountant = ChurchMember::whereId(JumuiyaAccountant::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->pluck('church_member_id'))->pluck('full_name');
                                Notification::make()
                                ->title($data['role'].' has already been assigned to '. $jumuiya_accountant[0])
                                ->body('Unassign current jumuiya chairperson'. $jumuiya_accountant[0]. 'in order to assign new Chairperson for jumuiya')
                                ->warning()
                                ->send();
                            }else{
                                $user = User::whereId($record->user_id)->first(); 
                                if($data['role'] == 'Jumuiya Chairperson'){
                                    $jumuiya_chairperson = new JumuiyaChairperson;
                                    $jumuiya_chairperson->church_member_id = $record->id;
                                    $jumuiya_chairperson->jumuiya_id = $record->jumuiya_id;
                                    $jumuiya_chairperson->date_registered = now();
                                    $jumuiya_chairperson->status = 'active';
                                    $jumuiya_chairperson->save();

                                    $user->assignRole($data['role']);

                                    Notification::make()
                                    ->title('Assigned '.$record->full_name.' as '.$data['role'])
                                    ->success()
                                    ->send();
                                }else if($data['role'] == 'Jumuiya Accountant'){
                                    $jumuiya_accountant = new JumuiyaAccountant;
                                    $jumuiya_accountant->church_member_id = $record->id;
                                    $jumuiya_accountant->jumuiya_id = $record->jumuiya_id;
                                    $jumuiya_accountant->date_registered = now();
                                    $jumuiya_accountant->status = 'active';
                                    $jumuiya_accountant->save();

                                    $user->assignRole($data['role']);

                                    Notification::make()
                                    ->title('Assigned '.$record->full_name.' as '.$data['role'])
                                    ->success()
                                    ->send();
                                }
                            }

                        })
                        ->visible(function(Model $record){
                            if($record->status == 'active'){
                                if((JumuiyaChairperson::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->exists() && JumuiyaAccountant::where('jumuiya_id', auth()->user()->churchMember->jumuiya_id)->where('status', 'active')->exists())){
                                    return false;
                                }else{
                                    if(auth()->user()->hasRole('Committee Member') && (auth()->user()->checkPermissionTo('create JumuiyaChairPerson ') || auth()->user()->checkPermissionTo('create JumuiyaAccountant'))){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }
                            }else{
                                return false;
                            }
                        }),

                        Action::make('unassign_role')
                        ->requiresConfirmation()
                        ->action(function(Model $record){
                            $user = User::whereId($record->user_id)->first();
                            $roles = $user->getRoleNames()->toArray();
                            if(in_array('Jumuiya Chairperson', $roles)){
                                $user->removeRole('Jumuiya Chairperson');

                                Notification::make()
                                ->title('Successfully unassigned '.$record->full_name.' as Jumuiya Chairperson.')
                                ->body('Please, specify or assign new Jumuiya Chairperson.')
                                ->send();
                            }else if(in_array('Jumuiya Accountant', $roles)){
                                $user->removeRole('Jumuiya Accountant');

                                Notification::make()
                                ->title('Successfully unassigned '.$record->full_name.' as Jumuiya Accountant.')
                                ->body('Please, specify or assign new Jumuiya Accountant.')
                                ->send();
                            }
                        })
                        ->visible(function(Model $record){
                            $user = User::whereId($record->user_id)->first();
                            if($user->hasRole('Jumuiya Chairperson') || $user->hasRole('Jumuiya Accountant')){
                                if(auth()->user()->hasRole('Committee Member')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }else{
                                return true;
                            }
                        })
                ]);
    }
}
