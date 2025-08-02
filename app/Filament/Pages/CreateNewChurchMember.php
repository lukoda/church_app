<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ChurchMember;
use Filament\Forms\Form;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Models\Region;
use App\Models\District;
use App\Models\Diocese;
use App\Models\Ward;
use App\Models\Jumuiya;
use App\Models\Card;
use App\Models\CardPledge;
use App\Models\User;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use App\Models\Church;
use App\Models\ChurchDistrict;

class CreateNewChurchMember extends Page
{    
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.create-new-church-member';

    protected static ?string $navigationGroup = 'ChurchMember';

    protected static ?string $title = 'Member Details';

    public ChurchMember $church_member;

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return Auth::guard('web')->user()->churchMember ? false : true;
    }

    public function mountCanAuthorizeAccess(): void
    {
        if(static::canAccess()){
            abort_unless(static::canAccess(), 403);
            // if(auth()->user()->churchMember->pledges->count() > 0){
            //     if(auth()->user()->churchMember->where('status', 'active')->count() > 0){
            //         abort_unless(static::canAccess(), 403);
            //     }else if(auth()->user()->churchMember->whereNull('status')->count() > 0){
            //         if(auth()->user()->churchMember->whereNotNull('jumuiya_id')){
            //             Notification::make()
            //             ->title('Registration Complete')
            //             ->body('Please contact your jumuiya chairperson for verification.')
            //             ->warning()
            //             ->send();
            //         }else{
            //             Notification::make()
            //             ->title('Registration Complete')
            //             ->body('Please contact your church secretary for verification.')
            //             ->warning()
            //             ->send();
            //         }
            //         Notification::make()
            //         ->title('Registration Complete')
            //         ->body('Please contact your administrator.')
            //         ->warning()
            //         ->send();
            //         redirect()->to('/admin');
            //     }else{
            //         Notification::make()
            //         ->title('Not verified as member')
            //         ->body('Please contact the church secretary for more information.')
            //         ->danger()
            //         ->send();
            //         redirect()->to('/admin');
            //     }
            // }else{
            //     Notification::make()
            //     ->title('Not verified as member')
            //     ->body('Please contact the church secretary for more information.')
            //     ->danger()
            //     ->send();
            //     redirect()->to('/admin');
            // }
            // abort_unless(static::canAccess(), 403);
        }else{
            Notification::make()
            ->title('Access Denied')
            ->body('You cant create more than one church member.Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->churchMember ? false : true;
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
                ->schema([
                    Wizard::make()
                    ->columnSpanFull()
                    ->steps([
                        Step::make('Personal Information')
                            ->columns(3)
                            ->schema([

                                TextInput::make('first_name')
                                    ->required(),

                                TextInput::make('middle_name')
                                    ->required(),

                                TextInput::make('surname')
                                    ->required(),

                                TextInput::make('email')
                                    ->nullable()
                                    ->email(),

                                Select::make('gender')
                                    ->options([
                                        'Male' => 'Male',
                                        'Female' => 'Female'
                                    ])
                                    ->required(),

                                TextInput::make('phone')
                                    ->unique(modifyRuleUsing: function (Unique $rule, $state) {
                                        return $rule->where('phone', $state)->where('user_id', '!=', auth()->user()->id);
                                    })
                                    ->tel()
                                    ->helperText('0789******')
                                    ->maxLength(10)
                                    ->required()
                                    ->default(User::whereId(auth()->id())->pluck('phone')[0])
                                    ->readOnly(),

                                Select::make('marital_status')
                                    ->options([
                                        'Married' => 'Married',
                                        'Single' => 'Single',
                                        'Divorced' => 'Divorced',
                                        'Widow' => 'Widow',
                                        'Widower' => 'Widower'
                                    ])
                                    ->reactive()
                                    ->required(),

                                DatePicker::make('date_of_birth')
                                    ->required(),

                                Hidden::make('user_id')
                                    ->default(auth()->user()->id),

                                Hidden::make('church_id')
                                    ->default(auth()->user()->church_id),

                                Section::make('Spouse Information')
                                    ->schema([
                                        TextInput::make('spouse_name')
                                            ->required(),

                                        TextInput::make('spouse_contact_no')
                                            ->tel()
                                            ->maxLength(13)
                                            ->helperText('0789******')
                                            ->maxLength(10)
                                            ->required(),
                                    ])
                                    ->columns(2)
                                    ->visible(function(Get $get){
                                        if($get('marital_status') == 'Married'){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),

                                Repeater::make('dependants')
                                    ->schema([
                                        TextInput::make('first_name')
                                            ->label('First Name')
                                            ->reactive()
                                            ->afterStateUpdated(function(Set $set, Get $get){
                                                $set('middle_name', $get('../../middle_name'));
                                                $set('surname', $get('../../surname'));
                                            }),

                                        TextInput::make('middle_name'),

                                        TextInput::make('surname'),

                                        Select::make('gender')
                                            ->options([
                                                'Female' => 'Female',
                                                'Male' => 'Male'
                                            ])
                                            ->required(function(Get $get){
                                                if($get('first_name')){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),

                                        DatePicker::make('date_of_birth')
                                            ->native(false)
                                            ->required(function(Get $get){
                                                if($get('name')){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),

                                        Select::make('relationship')
                                            ->options([
                                                'son'      => 'Son',
                                                'daughter' => 'Daughter',
                                                'cousin'   => 'Cousin',
                                                'nephew'   => 'Nephew',
                                                'niece'    => 'Niece',
                                                'father'   => 'Father',
                                                'mother'   => 'Mother',
                                                'Brother'  => 'Brother',
                                                'sister'   => 'Sister',
                                                'relative' => 'Relatives'
                                            ])
                                            ->required(function(Get $get){
                                                if($get('first_name')){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),


                                    ])
                                    ->collapsible()
                                    ->addActionLabel('Add Dependants')
                                    ->columnSpan('full')
                                    ->columns(4)

                                ]),

                        Step::make('identifications')
                            ->columns(2)
                            ->schema([
                                Select::make('identification_type')
                                    ->options([
                                        'nida' => 'Nida',
                                        'passport' => 'passport'
                                    ])
                                    ->reactive(),

                                TextInput::make('nida_id')
                                    ->required()
                                    ->visible(function(Get $get){
                                        if($get('identification_type') == 'nida'){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),

                                TextInput::make('passport_id')
                                    ->required()
                                    ->visible(function(Get $get){
                                        if($get('identification_type') == 'passport'){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),

                                FileUpload::make('picture')
                                    ->label('Member Image')
                                    ->downloadable()
                                    ->nullable()
                                    ->columnSpan('full'),
                                ])
                                ->afterValidation(function () {
                                    if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                        $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                        $church_member->update([
                                            'first_name' => $this->form->getState()['first_name'],
                                            'middle_name' => $this->form->getState()['middle_name'],
                                            'surname' => $this->form->getState()['surname'],
                                            'email' => $this->form->getState()['email'] ?? Null,
                                            'phone' => $this->form->getState()['phone'],
                                            'gender' => $this->form->getState()['gender'],
                                            'marital_status' => $this->form->getState()['marital_status'],
                                            'date_of_birth' => $this->form->getState()['date_of_birth'],
                                            'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                                            'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                                            'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                                            'picture' => $this->form->getState()['picture'] ?? Null,
                                            'user_id' => auth()->user()->id,
                                            'church_id' => auth()->user()->church_id
                                        ]);
                                    }else{
                                        $church_member = new ChurchMember;
                                        $church_member->first_name = $this->form->getState()['first_name'];
                                        $church_member->middle_name = $this->form->getState()['middle_name'];
                                        $church_member->surname = $this->form->getState()['surname'];
                                        $church_member->email = $this->form->getState()['email'] ?? Null;
                                        $church_member->phone = $this->form->getState()['phone'];
                                        $church_member->gender = $this->form->getState()['gender'];
                                        $church_member->marital_status = $this->form->getState()['marital_status'];
                                        $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
                                        $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
                                        $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
                                        $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
                                        $church_member->picture = $this->form->getState()['picture'] ?? Null;
                                        $church_member->user_id = auth()->user()->id;
                                        $church_member->church_id = auth()->user()->church_id;
                                        $church_member->save();
                                    }
                                }),

                        Step::make('Address Details')
                            ->schema([
                            Grid::make(3)
                                ->schema([
                                    TextInput::make('postal_code')
                                        ->nullable()
                                        ->hidden(),

                                    Select::make('region_id')
                                        ->preload()
                                        ->reactive()
                                        ->searchable()
                                        ->label('Region')
                                        ->options(function(){
                                            $church_district_id = Church::where('id', auth()->user()->church_id)->pluck('church_district_id');
                                            $diocese_id = ChurchDistrict::whereIn('id', $church_district_id)->pluck('diocese_id');
                                            $regions = Diocese::whereIn('id', $diocese_id)->pluck('regions')->collapse();

                                            return Region::whereIn('name', $regions)->pluck('name', 'id');
                                        })
                                        ->afterStateUpdated(function (Set $set): void {
                                            $set('district_id', null);
                                            $set('ward_id', null);
                                        })
                                        ->required(function(){
                                                if(auth()->user()->churchMember){
                                                    if(auth()->user()->churchMember->personal_details !== Null){
                                                        return true;
                                                    }else{
                                                        return false;
                                                    }
                                                }else{
                                                    return false;
                                                }
                                        }),

                                    Select::make('district_id')
                                        ->preload()
                                        ->searchable()
                                        ->label('District')
                                        ->options(function (Get $get) {
                                            if (blank($get('region_id'))) {
                                                return [];
                                            }

                                            $region = Region::whereId($get('region_id'))->first();

                                            return $region->districts()->pluck('name', 'id')->toArray();
                                        })
                                        ->reactive()
                                        ->required(function(){
                                                if(auth()->user()->churchMember){
                                                    if(auth()->user()->churchMember->personal_details !== Null){
                                                        return true;
                                                    }else{
                                                        return false;
                                                    }
                                                }else{
                                                    return false;
                                                }
                                        }),

                                    Select::make('ward_id')
                                        ->preload()
                                        ->searchable()
                                        ->label('Ward')
                                        ->options(function (Get $get) {
                                            if (blank($get('district_id'))) {
                                                return [];
                                            }

                                            $district = District::whereId($get('district_id'))->first();

                                            return $district->wards()->pluck('name', 'id')->toArray();
                                        })
                                        ->required(function(){
                                                if(auth()->user()->churchMember){
                                                    if(auth()->user()->churchMember->personal_details !== Null){
                                                        return true;
                                                    }else{
                                                        return false;
                                                    }
                                                }else{
                                                    return false;
                                                }
                                        }),

                                        TextInput::make('street')
                                            ->nullable(),

                                        TextInput::make('block_no')
                                            ->nullable(),

                                        TextInput::make('house_no')
                                            ->nullable(),
                                    ]),

                                ])
                                ->afterValidation(function () {
                                    if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                        $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                        $church_member->update([
                                            'first_name' => $this->form->getState()['first_name'],
                                            'middle_name' => $this->form->getState()['middle_name'],
                                            'surname' => $this->form->getState()['surname'],
                                            'email' => $this->form->getState()['email'] ?? Null,
                                            'phone' => $this->form->getState()['phone'],
                                            'gender' => $this->form->getState()['gender'],
                                            'marital_status' => $this->form->getState()['marital_status'],
                                            'date_of_birth' => $this->form->getState()['date_of_birth'],
                                            'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                                            'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                                            'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                                            'picture' => $this->form->getState()['picture'] ?? Null,
                                            'postal_code' => $this->form->getState()['postal_code'] ?? Null,
                                            'region_id' => $this->form->getState()['region_id'] ?? Null,
                                            'district_id' => $this->form->getState()['district_id'] ?? Null,
                                            'ward_id' => $this->form->getState()['ward_id'] ?? Null,
                                            'street' => $this->form->getState()['street'] ?? Null,
                                            'block_no' => $this->form->getState()['block_no'] ?? Null,
                                            'house_no' => $this->form->getState()['house_no'] ?? Null,
                                            'address_details' => is_null($this->form->getState()['region_id']) ? Null : 'complete',
                                            'user_id' => auth()->user()->id,
                                            'church_id' => auth()->user()->church_id
                                        ]);
                                    }else{
                                        $church_member = new ChurchMember;
                                        $church_member->first_name = $this->form->getState()['first_name'];
                                        $church_member->middle_name = $this->form->getState()['middle_name'];
                                        $church_member->surname = $this->form->getState()['surname'];
                                        $church_member->email = $this->form->getState()['email'] ?? Null;
                                        $church_member->phone = $this->form->getState()['phone'];
                                        $church_member->gender = $this->form->getState()['gender'];
                                        $church_member->marital_status = $this->form->getState()['marital_status'];
                                        $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
                                        $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
                                        $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
                                        $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
                                        $church_member->picture = $this->form->getState()['picture'] ?? Null;
                                        $church_member->postal_code = $this->form->getState()['postal_code'] ?? Null;
                                        $church_member->region_id = $this->form->getState()['region_id'] ?? Null;
                                        $church_member->district_id = $this->form->getState()['district_id'] ?? Null;
                                        $church_member->ward_id = $this->form->getState()['ward_id'] ?? Null;
                                        $church_member->street = $this->form->getState()['street'] ?? Null;
                                        $church_member->block_no = $this->form->getState()['block_no'] ?? Null;
                                        $church_member->house_no = $this->form->getState()['house_no'] ?? Null;
                                        $church_member->address_details = is_null($this->form->getState()['region_id']) ? Null : 'complete';
                                        $church_member->user_id = auth()->user()->id;
                                        $church_member->church_id = auth()->user()->church_id;
                                        $church_member->save();
                                    }
                                }),

                        Step::make('Spiritual Information')
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Section::make('Jumuiya Details')
                                    ->schema([
                                        Select::make('jumuiya_id')
                                            ->label('Jumuiya')
                                            ->searchable()
                                            ->options(function(Get $get){
                                                if(! blank($get('ward_id'))){
                                                    if(Jumuiya::where('ward', Ward::whereId($get('ward_id'))->pluck('name'))->count() > 0){
                                                        return Jumuiya::where('ward', Ward::whereId($get('ward_id'))->pluck('name')[0])->pluck('name', 'id');
                                                    }else{
                                                        return Jumuiya::where('district', District::whereId($get('district_id'))->pluck('name')[0])->pluck('name', 'id');
                                                    }
                                                }else{
                                                    return [];
                                                }
                                            }),

                                        TextInput::make('jumuiya_location_scope')
                                            ->default(function(Get $get){
                                                return Jumuiya::whereId($get('jumuiya'))->pluck('ward');
                                            })
                                            ->visible(function(Get $get){
                                                if(blank($get('jumuiya'))){
                                                    return false;
                                                }else{
                                                    return true;
                                                }
                                            })
                                            ->disabled()
                                    ]),

                                Checkbox::make('received_confirmation')
                                    ->required(function(){
                                            if(auth()->user()->churchMember){
                                                if(auth()->user()->churchMember->address_details == 'complete'){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }else{
                                                return false;
                                            }
                                    })->reactive(),

                                Section::make('Confirmation Place')
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('confirmation_place')
                                            ->nullable(),

                                        DatePicker::make('confirmation_date')
                                            ->nullable()
                                            ->native(false),
                                    ])
                                    ->visible(function(Get $get){
                                        if($get('received_confirmation')){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),

                                Checkbox::make('received_baptism')
                                ->required(function(){
                                        if(auth()->user()->churchMember){
                                            if(auth()->user()->churchMember->address_details == 'complete'){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }else{
                                            return false;
                                        }
                                })->reactive(),

                                Section::make('Baptism Place')
                                    ->columns(2)
                                    ->columnSpanFull()
                                    ->collapsible()
                                    ->schema([
                                        TextInput::make('baptism_place')
                                            ->nullable(),

                                        DatePicker::make('baptism_date')
                                            ->nullable()
                                            ->native(false)
                                    ])
                                    ->visible(function(Get $get){
                                        if($get('received_baptism')){
                                            return true;
                                        }else{
                                            return false;
                                        }
                                    }),

                                Section::make('Other Details')
                                    ->columns(3)
                                    ->columnSpanFull()
                                    ->schema([
                                        Select::make('volunteering_in')
                                            ->options([
                                                'fellowship' => 'Fellowship',
                                                'kwaya' => 'kwaya',
                                                'usafi' => 'usafi',
                                                'uwalimu watoto' => 'uwalimu watoto',
                                                'upambaji' => 'upambaji'
                                            ]),

                                        Select::make('sacrament_participation')
                                            ->options([
                                                'yes' => 'Yes',
                                                'no' => 'No'
                                            ]),

                                        TextInput::make('previous_church')
                                                ->nullable(),

                                        Section::make('Education And Professional Information')
                                                ->columns(4)
                                                ->schema([
                                                    TextInput::make('education_level')
                                                        ->nullable(),

                                                    TextInput::make('profession')
                                                        ->nullable(),

                                                    TextInput::make('skills')
                                                        ->nullable()
                                                        ->helperText('If multiple separate by comma\'s(,)'),

                                                    TextInput::make('work_location')
                                                        ->nullable(),

                                                    Hidden::make('status')
                                                        ->default('inactive'),

                                                ])
                                    ])
                        ])
                        ->afterValidation(function () {
                            if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                $church_member->update([
                                    'first_name' => $this->form->getState()['first_name'],
                                    'middle_name' => $this->form->getState()['middle_name'],
                                    'surname' => $this->form->getState()['surname'],
                                    'email' => $this->form->getState()['email'] ?? Null,
                                    'phone' => $this->form->getState()['phone'],
                                    'gender' => $this->form->getState()['gender'],
                                    'marital_status' => $this->form->getState()['marital_status'],
                                    'date_of_birth' => $this->form->getState()['date_of_birth'],
                                    'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                                    'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                                    'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                                    'picture' => $this->form->getState()['picture'] ?? Null,
                                    'postal_code' => $this->form->getState()['postal_code'] ?? Null,
                                    'region_id' => $this->form->getState()['region_id'] ?? Null,
                                    'district_id' => $this->form->getState()['district_id'] ?? Null,
                                    'ward_id' => $this->form->getState()['ward_id'] ?? Null,
                                    'street' => $this->form->getState()['street'] ?? Null,
                                    'block_no' => $this->form->getState()['block_no'] ?? Null,
                                    'house_no' => $this->form->getState()['house_no'] ?? Null,
                                    'address_details' => is_null($this->form->getState()['region_id']) ? Null : 'complete',
                                    'jumuiya_id' => $this->form->getState()['jumuiya_id'] ?? Null,
                                    'received_confirmation' => $this->form->getState()['received_confirmation'] ?? Null,
                                    'confirmation_place' => $this->form->getState()['confirmation_place'] ?? Null,
                                    'confirmation_date' => $this->form->getState()['confirmation_date'] ?? Null,
                                    'received_baptism' => $this->form->getState()['received_baptism'] ?? Null,
                                    'baptism_place' => $this->form->getState()['baptism_place'] ?? Null,
                                    'baptism_date' => $this->form->getState()['baptism_date'] ?? Null,
                                    'volunteering_in' => $this->form->getState()['volunteering_in'] ?? Null,
                                    'sacrament_participation' => $this->form->getState()['sacrament_participation'] ?? Null,
                                    'previous_church' => $this->form->getState()['previous_church'] ?? Null,
                                    'education_level' => $this->form->getState()['education_level'] ?? Null,
                                    'profession' => $this->form->getState()['profession'] ?? Null,
                                    'skills' => $this->form->getState()['skills'] ?? Null,
                                    'work_location' => $this->form->getState()['work_location'] ?? Null,
                                    'spiritual_information' => is_null($this->form->getState()['received_baptism']) ? Null : 'complete',
                                    'user_id' => auth()->user()->id,
                                    'church_id' => auth()->user()->church_id
                                ]);
                            }else{
                                $church_member = new ChurchMember;
                                $church_member->first_name = $this->form->getState()['first_name'];
                                $church_member->middle_name = $this->form->getState()['middle_name'];
                                $church_member->surname = $this->form->getState()['surname'];
                                $church_member->email = $this->form->getState()['email'] ?? Null;
                                $church_member->phone = $this->form->getState()['phone'];
                                $church_member->gender = $this->form->getState()['gender'];
                                $church_member->marital_status = $this->form->getState()['marital_status'];
                                $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
                                $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
                                $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
                                $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
                                $church_member->picture = $this->form->getState()['picture'] ?? Null;
                                $church_member->postal_code = $this->form->getState()['postal_code'] ?? Null;
                                $church_member->region_id = $this->form->getState()['region_id'] ?? Null;
                                $church_member->district_id = $this->form->getState()['district_id'] ?? Null;
                                $church_member->ward_id = $this->form->getState()['ward_id'] ?? Null;
                                $church_member->street = $this->form->getState()['street'] ?? Null;
                                $church_member->block_no = $this->form->getState()['block_no'] ?? Null;
                                $church_member->house_no = $this->form->getState()['house_no'] ?? Null;
                                $church_member->address_details = is_null($this->form->getState()['region_id']) ? Null : 'complete';
                                $church_member->jumuiya_id = $this->form->getState()['jumuiya_id'] ?? Null;
                                $church_member->received_confirmation = $this->form->getState()['received_confirmation'] ?? Null;
                                $church_member->confirmation_place = $this->form->getState()['confirmation_place'] ?? Null;
                                $church_member->confirmation_date = $this->form->getState()['confirmation_date'] ?? Null;
                                $church_member->received_baptism = $this->form->getState()['received_baptism'] ?? Null;
                                $church_member->baptism_place = $this->form->getState()['baptism_place'] ?? Null;
                                $church_member->baptism_date = $this->form->getState()['baptism_date'] ?? Null;
                                $church_member->volunteering_in = $this->form->getState()['volunteering_in'] ?? Null;
                                $church_member->sacrament_participation = $this->form->getState()['sacrament_participation'] ?? Null;
                                $church_member->previous_church = $this->form->getState()['previous_church'] ?? Null;
                                $church_member->education_level = $this->form->getState()['education_level'] ?? Null;
                                $church_member->profession = $this->form->getState()['profession'] ?? Null;
                                $church_member->skills = $this->form->getState()['skills'] ?? Null;
                                $church_member->work_location = $this->form->getState()['work_location'] ?? Null;
                                $church_member->spiritual_information = is_null($this->form->getState()['received_baptism']) ? Null : 'complete';
                                $church_member->user_id = auth()->user()->id;
                                $church_member->church_id = auth()->user()->church_id;
                                $church_member->save();
                            }
                        }),

                        Step::make('Card Pledge Information')
                            ->schema([

                                Checkbox::make('is_NewMember')
                                    ->default(false)
                                    ->reactive()
                                    ->required(function(){
                                            if(auth()->user()->churchMember){
                                                if(auth()->user()->churchMember->spiritual_information !== Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }else{
                                                return false;
                                            }
                                    })->inline(false),

                                TextInput::make('card_no')
                                        ->required(function(){
                                            if(auth()->user()->churchMember){
                                                if(auth()->user()->churchMember->spiritual_information !== Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }else{
                                                return false;
                                            }
                                    })
                                        ->unique(modifyRuleUsing: function(Unique $rule, callable $get){
                                            return $rule->where('card_no', $get('card_no'))
                                                        ->where('church_id', $get('church_id'));
                                        }, ignoreRecord:true)
                                        ->visible(function(Get $get){
                                            if($get('is_NewMember')){
                                                return false;
                                            }else{
                                                return true;
                                            }
                                        }),

                                Repeater::make('Card Pledges')
                                    ->schema([
                                            Select::make('card_type')
                                                ->options(Card::where('church_id', auth()->user()->church_id)->pluck('card_name', 'id')->toArray())
                                                ->searchable()
                                                ->distinct(),

                                            TextInput::make('amount_pledged')
                                                ->numeric(),

                                        ])
                                        ->columnSpan('full')
                                        ->columns(2),
                            ])
                            ->afterValidation(function () {
                                if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                    $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                    $church_member->update([
                                        'first_name' => $this->form->getState()['first_name'],
                                        'middle_name' => $this->form->getState()['middle_name'],
                                        'surname' => $this->form->getState()['surname'],
                                        'email' => $this->form->getState()['email'] ?? Null,
                                        'phone' => $this->form->getState()['phone'],
                                        'gender' => $this->form->getState()['gender'],
                                        'marital_status' => $this->form->getState()['marital_status'],
                                        'date_of_birth' => $this->form->getState()['date_of_birth'],
                                        'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                                        'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                                        'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                                        'picture' => $this->form->getState()['picture'] ?? Null,
                                        'postal_code' => $this->form->getState()['postal_code'] ?? Null,
                                        'region_id' => $this->form->getState()['region_id'] ?? Null,
                                        'district_id' => $this->form->getState()['district_id'] ?? Null,
                                        'ward_id' => $this->form->getState()['ward_id'] ?? Null,
                                        'street' => $this->form->getState()['street'] ?? Null,
                                        'block_no' => $this->form->getState()['block_no'] ?? Null,
                                        'house_no' => $this->form->getState()['house_no'] ?? Null,
                                        'address_details' => is_null($this->form->getState()['region_id']) ? Null : 'complete',
                                        'jumuiya_id' => $this->form->getState()['jumuiya_id'] ?? Null,
                                        'received_confirmation' => $this->form->getState()['received_confirmation'] ?? Null,
                                        'confirmation_place' => $this->form->getState()['confirmation_place'] ?? Null,
                                        'confirmation_date' => $this->form->getState()['confirmation_date'] ?? Null,
                                        'received_baptism' => $this->form->getState()['received_baptism'] ?? Null,
                                        'baptism_place' => $this->form->getState()['baptism_place'] ?? Null,
                                        'baptism_date' => $this->form->getState()['baptism_date'] ?? Null,
                                        'volunteering_in' => $this->form->getState()['volunteering_in'] ?? Null,
                                        'sacrament_participation' => $this->form->getState()['sacrament_participation'] ?? Null,
                                        'previous_church' => $this->form->getState()['previous_church'] ?? Null,
                                        'education_level' => $this->form->getState()['education_level'] ?? Null,
                                        'profession' => $this->form->getState()['profession'] ?? Null,
                                        'skills' => $this->form->getState()['skills'] ?? Null,
                                        'work_location' => $this->form->getState()['work_location'] ?? Null,
                                        'spiritual_information' => is_null($this->form->getState()['received_baptism']) ? Null : 'complete',
                                        'is_NewMember' => $this->form->getState()['is_NewMember'] ?? Null,
                                        'card_no' => $this->form->getState()['card_no'] ?? Null,
                                        'user_id' => auth()->user()->id,
                                        'church_id' => auth()->user()->church_id
                                    ]);
                                }else{
                                    $church_member = new ChurchMember;
                                    $church_member->first_name = $this->form->getState()['first_name'];
                                    $church_member->middle_name = $this->form->getState()['middle_name'];
                                    $church_member->surname = $this->form->getState()['surname'];
                                    $church_member->email = $this->form->getState()['email'] ?? Null;
                                    $church_member->phone = $this->form->getState()['phone'];
                                    $church_member->gender = $this->form->getState()['gender'];
                                    $church_member->marital_status = $this->form->getState()['marital_status'];
                                    $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
                                    $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
                                    $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
                                    $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
                                    $church_member->picture = $this->form->getState()['picture'] ?? Null;
                                    $church_member->postal_code = $this->form->getState()['postal_code'] ?? Null;
                                    $church_member->region_id = $this->form->getState()['region_id'] ?? Null;
                                    $church_member->district_id = $this->form->getState()['district_id'] ?? Null;
                                    $church_member->ward_id = $this->form->getState()['ward_id'] ?? Null;
                                    $church_member->street = $this->form->getState()['street'] ?? Null;
                                    $church_member->block_no = $this->form->getState()['block_no'] ?? Null;
                                    $church_member->house_no = $this->form->getState()['house_no'] ?? Null;
                                    $church_member->address_details = is_null($this->form->getState()['region_id']) ? Null : 'complete';
                                    $church_member->jumuiya_id = $this->form->getState()['jumuiya_id'] ?? Null;
                                    $church_member->received_confirmation = $this->form->getState()['received_confirmation'] ?? Null;
                                    $church_member->confirmation_place = $this->form->getState()['confirmation_place'] ?? Null;
                                    $church_member->confirmation_date = $this->form->getState()['confirmation_date'] ?? Null;
                                    $church_member->received_baptism = $this->form->getState()['received_baptism'] ?? Null;
                                    $church_member->baptism_place = $this->form->getState()['baptism_place'] ?? Null;
                                    $church_member->baptism_date = $this->form->getState()['baptism_date'] ?? Null;
                                    $church_member->volunteering_in = $this->form->getState()['volunteering_in'] ?? Null;
                                    $church_member->sacrament_participation = $this->form->getState()['sacrament_participation'] ?? Null;
                                    $church_member->previous_church = $this->form->getState()['previous_church'] ?? Null;
                                    $church_member->education_level = $this->form->getState()['education_level'] ?? Null;
                                    $church_member->profession = $this->form->getState()['profession'] ?? Null;
                                    $church_member->skills = $this->form->getState()['skills'] ?? Null;
                                    $church_member->work_location = $this->form->getState()['work_location'] ?? Null;
                                    $church_member->spiritual_information = is_null($this->form->getState()['received_baptism']) ? Null : 'complete';
                                    $church_member->is_NewMember = $this->form->getState()['is_NewMember'] ?? Null;
                                    $church_member->card_no = $this->form->getState()['card_no'] ?? Null;
                                    $church_member->user_id = auth()->user()->id;
                                    $church_member->church_id = auth()->user()->church_id;
                                    $church_member->save();
                                }
                            }),


                ]),
            ])
            ->statePath('data');
    }

    public function createChurchMember(): void
    {
        if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
            $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
            $church_member->update([
                'first_name' => $this->form->getState()['first_name'],
                'middle_name' => $this->form->getState()['middle_name'],
                'surname' => $this->form->getState()['surname'],
                'email' => $this->form->getState()['email'] ?? Null,
                'phone' => $this->form->getState()['phone'],
                'gender' => $this->form->getState()['gender'],
                'marital_status' => $this->form->getState()['marital_status'],
                'date_of_birth' => $this->form->getState()['date_of_birth'],
                'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                'picture' => $this->form->getState()['picture'] ?? Null,
                'postal_code' => $this->form->getState()['postal_code'] ?? Null,
                'region_id' => $this->form->getState()['region_id'] ?? Null,
                'district_id' => $this->form->getState()['district_id'] ?? Null,
                'ward_id' => $this->form->getState()['ward_id'] ?? Null,
                'street' => $this->form->getState()['street'] ?? Null,
                'block_no' => $this->form->getState()['block_no'] ?? Null,
                'house_no' => $this->form->getState()['house_no'] ?? Null,
                'address_details' => is_null($this->form->getState()['region_id']) ? Null : 'complete',
                'jumuiya_id' => $this->form->getState()['jumuiya_id'] ?? Null,
                'received_confirmation' => $this->form->getState()['received_confirmation'] ?? Null,
                'confirmation_place' => $this->form->getState()['confirmation_place'] ?? Null,
                'confirmation_date' => $this->form->getState()['confirmation_date'] ?? Null,
                'received_baptism' => $this->form->getState()['received_baptism'] ?? Null,
                'baptism_place' => $this->form->getState()['baptism_place'] ?? Null,
                'baptism_date' => $this->form->getState()['baptism_date'] ?? Null,
                'volunteering_in' => $this->form->getState()['volunteering_in'] ?? Null,
                'sacrament_participation' => $this->form->getState()['sacrament_participation'] ?? Null,
                'previous_church' => $this->form->getState()['previous_church'] ?? Null,
                'education_level' => $this->form->getState()['education_level'] ?? Null,
                'profession' => $this->form->getState()['profession'] ?? Null,
                'skills' => $this->form->getState()['skills'] ?? Null,
                'work_location' => $this->form->getState()['work_location'] ?? Null,
                'spiritual_information' => is_null($this->form->getState()['received_baptism']) ? Null : 'complete',
                'is_NewMember' => $this->form->getState()['is_NewMember'] ?? Null,
                'card_no' => $this->form->getState()['card_no'] ?? Null,
                'user_id' => auth()->user()->id,
                'church_id' => auth()->user()->church_id
            ]);
        }else{
            $church_member = new ChurchMember;
            $church_member->first_name = $this->form->getState()['first_name'];
            $church_member->middle_name = $this->form->getState()['middle_name'];
            $church_member->surname = $this->form->getState()['surname'];
            $church_member->email = $this->form->getState()['email'] ?? Null;
            $church_member->phone = $this->form->getState()['phone'];
            $church_member->gender = $this->form->getState()['gender'];
            $church_member->marital_status = $this->form->getState()['marital_status'];
            $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
            $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
            $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
            $church_member->picture = $this->form->getState()['picture'] ?? Null;
            $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
            $church_member->postal_code = $this->form->getState()['postal_code'] ?? Null;
            $church_member->region_id = $this->form->getState()['region_id'] ?? Null;
            $church_member->district_id = $this->form->getState()['district_id'] ?? Null;
            $church_member->ward_id = $this->form->getState()['ward_id'] ?? Null;
            $church_member->street = $this->form->getState()['street'] ?? Null;
            $church_member->block_no = $this->form->getState()['block_no'] ?? Null;
            $church_member->house_no = $this->form->getState()['house_no'] ?? Null;
            $church_member->address_details = is_null($this->form->getState()['region_id']) ? Null : 'complete';
            $church_member->jumuiya_id = $this->form->getState()['jumuiya_id'] ?? Null;
            $church_member->received_confirmation = $this->form->getState()['received_confirmation'] ?? Null;
            $church_member->confirmation_place = $this->form->getState()['confirmation_place'] ?? Null;
            $church_member->confirmation_date = $this->form->getState()['confirmation_date'] ?? Null;
            $church_member->received_baptism = $this->form->getState()['received_baptism'] ?? Null;
            $church_member->baptism_place = $this->form->getState()['baptism_place'] ?? Null;
            $church_member->baptism_date = $this->form->getState()['baptism_date'] ?? Null;
            $church_member->volunteering_in = $this->form->getState()['volunteering_in'] ?? Null;
            $church_member->sacrament_participation = $this->form->getState()['sacrament_participation'] ?? Null;
            $church_member->previous_church = $this->form->getState()['previous_church'] ?? Null;
            $church_member->education_level = $this->form->getState()['education_level'] ?? Null;
            $church_member->profession = $this->form->getState()['profession'] ?? Null;
            $church_member->skills = $this->form->getState()['skills'] ?? Null;
            $church_member->work_location = $this->form->getState()['work_location'] ?? Null;
            $church_member->spiritual_information = is_null($this->form->getState()['received_baptism']) ? Null : 'complete';
            $church_member->is_NewMember = $this->form->getState()['is_NewMember'] ?? Null;
            $church_member->card_no = $this->form->getState()['card_no'] ?? Null;
            $church_member->user_id = auth()->user()->id;
            $church_member->church_id = auth()->user()->church_id;
            $church_member->save();
        }


        if(count($this->form->getState()['Card Pledges']) > 0){

            foreach($this->form->getState()['Card Pledges'] as $pledge){
                if($pledge['card_type'] != Null && $pledge['amount_pledged'] != Null){
                    $card_pledge = new CardPledge;
                    $card_pledge->church_member_id = $church_member->id;
                    $card_pledge->card_id = $pledge['card_type'];
                    $card_pledge->card_no = $$church_member->card_no ?? Null;
                    $card_pledge->amount_pledged = $pledge['amount_pledged'];
                    $card_pledge->amount_remains = 0;
                    $card_pledge->amount_completed = 0;
                    $card_pledge->date_pledged = $church_member->created_at;
                    $card_pledge->created_by = auth()->user()->id;
                    $card_pledge->church_id = auth()->user()->church_id;
                    $card_pledge->status = 'Active';
                    $card_pledge->save();
                }
            }
        }

        redirect()->to('admin/members');
    }

}
