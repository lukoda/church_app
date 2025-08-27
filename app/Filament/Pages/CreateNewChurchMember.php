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
use App\Models\Country;
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
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\Blade;

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
                        Step::make('Person Details')
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
                                    ->placeHolder(function(){
                                        if(auth()->user()->residence_status == 'Resident'){
                                            return '0789******';
                                        }else if(auth()->user()->residence_status == 'Non Resident'){
                                            return '+1234xxxxxxx';
                                        }else{
                                            return '0789******';
                                        }
                                    })
                                    ->helperText('This will be member username for login once approved')
                                    ->maxLength(fn() => auth()->user()->residence_status == 'Resident' ? 10 : 20)
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
                                    ->required()
                                    ->maxDate(function(){
                                        $dob = Carbon::now();
                                        return $dob->subYears(12);
                                    })
                                    ->validationMessages([
                                        'error' => 'Only 12 years above are allowed to register as church member.',
                                    ]),

                                Select::make('citizenship')
                                    ->searchable()
                                    ->options(Country::all()->pluck('name','code'))
                                    ->required(),

                                FileUpload::make('picture')
                                    ->label('Passport Size')
                                    ->downloadable()
                                    ->nullable()
                                    ->columnSpan('full'),

                                Hidden::make('user_id')
                                    ->default(auth()->user()->id),

                                Hidden::make('church_id')
                                    ->default(auth()->user()->church_id),

                                Section::make('Spouse Information')
                                    ->schema([
                                        TextInput::make('spouse_first_name')
                                        ->required(),
    
                                        TextInput::make('spouse_middle_name')
                                        ->required(),
        
                                        TextInput::make('spouse_surname')
                                        ->required(),

                                        Select::make('spouse_citizenship')
                                        ->searchable()
                                        ->options(Country::all()->pluck('name','code'))
                                        ->required(),

                                        Select::make('spouse_residence_status')
                                        ->label('Residential Status')
                                        ->live()
                                        ->options([
                                            'Resident' => 'Resident',
                                            'Non Resident' => 'Non Resident'
                                        ])
                                        ->required(),

                                        TextInput::make('spouse_contact_no')
                                            ->tel()
                                            ->maxLength(fn(Get $get) => $get('spouse_residence_status') == 'Resident' ? 10 : 20)
                                            ->helperText(function(Get $get){
                                                if($get('spouse_residence_status') == 'Resident'){
                                                    return '0789******';
                                                }else if($get('spouse_residence_status') == 'Non Resident'){
                                                    return '+1234xxxxxxx';
                                                }else{
                                                    return '';
                                                }
                                            })
                                            ->maxLength(fn(Get $get) => $get('spouse_residence_status') == 'Resident' ? 10 : 20)
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

                                Checkbox::make('has_dependants')
                                ->label('Has Dependants?')
                                ->inline(false)
                                ->live(),

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
                                    ->hidden(fn (Get $get): bool => ! $get('has_dependants'))
                                ])
                                ->afterValidation(function(Get $get){

                                    if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                        $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                        $church_member->update([
                                            'first_name' => $get('first_name'),
                                            'middle_name' => $get('middle_name'),
                                            'surname' => $get('surname'),
                                            'email' => $get('email') ?? Null,
                                            'phone' => $get('phone'),
                                            'gender' => $get('gender'),
                                            'marital_status' => $get('marital_status'),
                                            'date_of_birth' => $get('date_of_birth'),
                                            'citizenship' => $get('citizenship') ?? Null,
                                            'spouse_first_name' => $get('spouse_first_name') ?? Null,
                                            'spouse_middle_name' => $get('spouse_middle_name') ?? Null,
                                            'spouse_surname' => $get('spouse_surname') ?? Null,
                                            'spouse_citizenship' => $get('spouse_citizenship') ?? Null,
                                            'spouse_residence_status' => $get('spouse_residence_status') ?? Null,
                                            'spouse_contact_no' => $get('spouse_contact_no') ?? Null,
                                            'personal_details' => is_null($get('first_name')) ? Null : 'complete',
                                            'user_id' => auth()->user()->id,
                                            'church_id' => auth()->user()->church_id
                                        ]);

                                        //add dependants
                                        $dependants =  $get('has_dependants') ? $get('dependants') : Null;
                                        if($dependants != Null){
                                            if(Dependant::where('church_member_id', $church_member->id)->exists()){
                                                //delete all dependants enter new data
                                                Dependant::where('church_member_id', $church_member->id)->delete();

                                                foreach($dependants as $dependant){
                                                    $member_dependants = new Dependant;
                                                    $member_dependants->first_name = $dependant['first_name'];
                                                    $member_dependants->middle_name = $dependant['middle_name'];
                                                    $member_dependants->surname = $dependant['surname'];
                                                    $member_dependants->gender = $dependant['gender'];
                                                    $member_dependants->date_of_birth = $dependant['date_of_birth'];
                                                    $member_dependants->relationship = $dependant['relationship'];
                                                    $member_dependants->church_member_id = $church_member->id;
                                                    $member_dependants->save();
                                                }

                                            }else{
                                                foreach($dependants as $dependant){
                                                    $member_dependants = new Dependant;
                                                    $member_dependants->first_name = $dependant['first_name'];
                                                    $member_dependants->middle_name = $dependant['middle_name'];
                                                    $member_dependants->surname = $dependant['surname'];
                                                    $member_dependants->gender = $dependant['gender'];
                                                    $member_dependants->date_of_birth = $dependant['date_of_birth'];
                                                    $member_dependants->relationship = $dependant['relationship'];
                                                    $member_dependants->church_member_id = $church_member->id;
                                                    $member_dependants->save();
                                                }
                                            }
                                        }

                                    }else{
                                        //check if user with same name exists
                                        $newChurchMemberName = $get('first_name').' '.$get('middle_name').' '.$get('surname');
                                        if(ChurchMember::where('full_name', $newChurchMemberName)->exists()){
                                            Notification::make()
                                            ->title('Member Exists')
                                            ->body('Member with same name already exists.')
                                            ->danger()
                                            ->send();

                                            throw new Halt();
                                        }else{
                                            $church_member = new ChurchMember;
                                            $church_member->first_name = $get('first_name') ?? Null;
                                            $church_member->middle_name = $get('middle_name') ?? Null;
                                            $church_member->surname = $get('surname') ?? Null;
                                            $church_member->email = $get('email') ?? Null;
                                            $church_member->phone = $get('phone') ?? Null;
                                            $church_member->gender = $get('gender') ?? Null;
                                            $church_member->marital_status = $get('marital_status') ?? Null;
                                            $church_member->date_of_birth = $get('date_of_birth') ?? Null;
                                            $church_member->citizenship = $get('citizenship') ?? Null;
                                            $church_member->spouse_first_name = $get('spouse_first_name') ?? Null;
                                            $church_member->spouse_middle_name = $get('spouse_middle_name') ?? Null;
                                            $church_member->spouse_surname = $get('spouse_surname') ?? Null;
                                            $church_member->spouse_citizenship = $get('spouse_citizenship') ?? Null;
                                            $church_member->spouse_residence_status = $get('spouse_residence_status') ?? Null;
                                            $church_member->spouse_contact_no = $get('spouse_contact_no') ?? Null;
                                            $church_member->personal_details = is_null($get('first_name')) ? Null : 'complete';
                                            $church_member->user_id = auth()->user()->id;
                                            $church_member->church_id = auth()->user()->church_id;
                                            $church_member->save();

                                            $dependants =  $get('has_dependants') ? $get('dependants') : Null;

                                            if($dependants != Null){
                                                foreach($dependants as $dependant){
                                                    $member_dependants = new Dependant;
                                                    $member_dependants->first_name = $dependant['first_name'];
                                                    $member_dependants->middle_name = $dependant['middle_name'];
                                                    $member_dependants->surname = $dependant['surname'];
                                                    $member_dependants->gender = $dependant['gender'];
                                                    $member_dependants->date_of_birth = $dependant['date_of_birth'];
                                                    $member_dependants->relationship = $dependant['relationship'];
                                                    $member_dependants->church_member_id = $church_member->id;
                                                    $member_dependants->save();
                                                }
                                            }

                                        }
                                    }
                                }),

                        Step::make('Physical Address')
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
                                            // $church_district_id = Church::where('id', auth()->user()->church_id)->pluck('church_district_id');
                                            // $diocese_id = ChurchDistrict::whereIn('id', $church_district_id)->pluck('diocese_id');
                                            // $regions = Diocese::whereIn('id', $diocese_id)->pluck('regions')->collapse();

                                            return Region::all()->pluck('name', 'id');
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
                                        })
                                        ->visible(function(){
                                            if(auth()->user()->residence_status == 'Non Resident'){
                                                return false;
                                            }else if(auth()->user()->residence_status == 'Resident'){
                                                return true;
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
                                        })
                                        ->visible(function(){
                                            if(auth()->user()->residence_status == 'Non Resident'){
                                                return false;
                                            }else if(auth()->user()->residence_status == 'Resident'){
                                                return true;
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
                                        })
                                        ->visible(function(){
                                            if(auth()->user()->residence_status == 'Non Resident'){
                                                return false;
                                            }else if(auth()->user()->residence_status == 'Resident'){
                                                return true;
                                            }
                                        }),

                                        TextInput::make('street')
                                            ->nullable()
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return false;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return true;
                                                }
                                            }),

                                        TextInput::make('block_no')
                                            ->nullable()
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return false;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return true;
                                                }
                                            }),

                                        TextInput::make('house_no')
                                            ->nullable()
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return false;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return true;
                                                }
                                            }),

                                        
                                        Select::make('resident_country')
                                            ->label('Resident Country')
                                            ->searchable()
                                            ->options(Country::all()->pluck('name','code'))
                                            ->required()
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return true;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return false;
                                                }
                                            }),

                                        TextInput::make('resident_city')
                                            ->label('Resident City')
                                            ->required()
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return true;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return false;
                                                }
                                            }),

                                        TextInput::make('resident_street')
                                        ->label('Resident Street')
                                        ->nullable()
                                        ->visible(function(){
                                            if(auth()->user()->residence_status == 'Non Resident'){
                                                return true;
                                            }else if(auth()->user()->residence_status == 'Resident'){
                                                return false;
                                            }
                                        }),


                                    ]),

                                ])
                                ->afterValidation(function (Get $get) {
                                    if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                        $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                        $church_member->update([
                                            'postal_code' => $get('postal_code') ?? Null,
                                            'region_id' => $get('region_id') ?? Null,
                                            'district_id' => $get('district_id') ?? Null,
                                            'ward_id' => $get('ward_id') ?? Null,
                                            'street' => $get('street') ?? Null,
                                            'block_no' => $get('block_no') ?? Null,
                                            'house_no' => $get('house_no') ?? Null,
                                            'resident_country' => $get('resident_country') ?? Null,
                                            'resident_city' => $get('resident_city') ?? Null,
                                            'resident_street' => $get('resident_street') ?? Null,
                                            'address_details' => (is_null($get('region_id')) && auth()->user()->residence_status == 'Resident') ? Null : ((is_null($get('resident_country')) && auth()->user()->residence_status == 'Non Resident') ? Null : 'complete'),
                                            'user_id' => auth()->user()->id,
                                            'church_id' => auth()->user()->church_id
                                        ]);
                                    }
                                }),

                                Step::make('ID Details')
                                ->columns(2)
                                ->schema([
                                    Select::make('identification_type')
                                        ->label('ID Type')
                                        ->required()
                                        ->options(function () {
                                            if(auth()->user()->residence_status == 'Non Resident'){
                                                return ['passport' => 'Passport'];
                                            }else{
                                               return  [
                                                    'nida' => 'NIDA',
                                                    'passport' => 'Passport',
                                                    'driving_license' => 'Driving License'
                                               ];
                                            }
                                        })
                                        ->reactive(),
    
                                    TextInput::make('nida_id')
                                        ->label('ID Number')
                                        ->placeHolder  ('eg. xxxxxxxx-xxxxx-xxxxx-xx')
                                        ->regex('/^\d{8}-\d{5}-\d{5}-\d{2}$/')
                                        ->maxLength(23)
                                        ->required()
                                        ->visible(function(Get $get){
                                            if($get('identification_type') == 'nida'){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }),
    
                                    TextInput::make('passport_id')
                                        ->label('Passport ID Number')
                                        ->required()
                                        ->visible(function(Get $get){
                                            if($get('identification_type') == 'passport'){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }),

                                    TextInput::make('driver_id')
                                        ->label('Driving Licence ID Number')
                                        ->required()
                                        ->visible(function(Get $get){
                                            if($get('identification_type') == 'driving_license'){
                                                return true;
                                            }else{
                                                return false;
                                            }
                                        }),
    
                                    FileUpload::make('id_image')
                                        ->label('ID Image')
                                        ->required()
                                        ->downloadable()
                                        ->nullable()
                                        ->columnSpan('full'),
                                    ])
                                    ->afterValidation(function (Get $get) {
                                        if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                            $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                            $church_member->update([
                                                'identification_type' => $get('identification_type') ?? Null,
                                                'nida_id' =>  $get('nida_id') ?? Null,
                                                'passport_id' => $get('passport_id') ?? Null,
                                                'driver_id' => $get('driver_id') ?? Null,
                                                // 'picture' => $get('picture') ?? Null,
                                                'id_image' => $get('id_image') ?? Null,
                                                'user_id' => auth()->user()->id,
                                                'church_id' => auth()->user()->church_id
                                            ]);
                                        }
                                    }),


                        Step::make('Other Information')
                            ->columns(2)
                            ->columnSpanFull()
                            ->schema([
                                Section::make('Church Communities')
                                    ->schema([
                                        Select::make('jumuiya_id')
                                            ->label('Communities')
                                            ->searchable()
                                            ->options(function(Get $get){
                                                if(! blank($get('ward_id'))){
                                                    if(Jumuiya::where('ward', Ward::whereId($get('ward_id'))->pluck('name'))->count() > 0){
                                                        return Jumuiya::where('ward', Ward::whereId($get('ward_id'))->pluck('name')[0])->where('church_id', auth()->user()->church_id)->pluck('name', 'id');
                                                    }else{
                                                        return [];
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
                                    ])
                                    ->hidden(function(){
                                        if(Jumuiya::all()->count() <= 0){
                                            return true;
                                        }else if($get('residence_status') == 'Non Resident'){
                                            return true;
                                        }else if($get('residence_status') == 'Resident'){
                                            return false;
                                        }else{
                                            return false;
                                        }
                                    }),

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
                                            ])
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return false;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return true;
                                                }
                                            }),

                                        Select::make('sacrament_participation')
                                            ->required()
                                            ->options([
                                                'yes' => 'Yes',
                                                'no' => 'No'
                                            ])
                                            ->visible(function(){
                                                if(auth()->user()->residence_status == 'Non Resident'){
                                                    return false;
                                                }else if(auth()->user()->residence_status == 'Resident'){
                                                    return true;
                                                }
                                            }),

                                        TextInput::make('previous_church')
                                                ->nullable(),

                                        Section::make('Education And Professional Information')
                                                ->columns(4)
                                                ->schema([
                                                    TextInput::make('education_level')
                                                        ->placeHolder('eg. Bachelor Degree')
                                                        ->nullable(),

                                                    TextInput::make('profession')
                                                        ->placeHolder('eg. Digital Marketing Engineer')
                                                        ->nullable(),

                                                    TextInput::make('skills')
                                                        ->placeHolder('eg. Content Creation')
                                                        ->nullable()
                                                        ->helperText('If multiple separate by comma\'s(,)'),

                                                    // TextInput::make('work_location')
                                                    //     ->nullable()
                                                    //     ->hidden(),

                                                    Hidden::make('status')
                                                        ->default('inactive'),

                                                ])
                                    ])
                        ])
                        ->afterValidation(function (Get $get) {
                            if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                $church_member->update([
                                    // 'first_name' => $get('first_name'),
                                    // 'middle_name' => $get('middle_name'),
                                    // 'surname' => $get('surname'),
                                    // 'email' => $get('email') ?? Null,
                                    // 'phone' => $get('phone'),
                                    // 'gender' => $get('gender'),
                                    // 'marital_status' => $get('marital_status'),
                                    // 'date_of_birth' => $get('date_of_birth'),
                                    // 'citizenship' => $get('citizenship'),
                                    // 'spouse_first_name' => $get('spouse_first_name') ?? Null,
                                    // 'spouse_middle_name' => $get('spouse_middle_name') ?? Null,
                                    // 'spouse_surname' => $get('spouse_surname') ?? Null,
                                    // 'spouse_citizenship' => $get('spouse_citizenship') ?? Null,
                                    // 'spouse_residence_status' => $get('spouse_residence_status') ?? Null,
                                    // 'spouse_contact_no' => $get('spouse_contact_no') ?? Null,
                                    // 'personal_details' => is_null($get('first_name')) ? Null : 'complete',
                                    // 'nida_id' => $get('nida_id') ?? Null,
                                    // 'passport_id' => $get('passport_id') ?? Null,
                                    // 'picture' => $get('picture') ?? Null,
                                    // 'postal_code' => $get('postal_code') ?? Null,
                                    // 'region_id' => $get('region_id') ?? Null,
                                    // 'district_id' => $get('district_id') ?? Null,
                                    // 'ward_id' => $get('ward_id') ?? Null,
                                    // 'street' => $get('street') ?? Null,$get('street'),
                                    // 'block_no' => $get('block_no') ?? Null,
                                    // 'house_no' => $get('house_no') ?? Null,
                                    // 'address_details' => (is_null($get('region_id')) && auth()->user()->residence_status == 'Resident') ? Null : ((is_null($get('resident_country')) && auth()->user()->residence_status == 'Non Resident') ? Null : 'complete'),
                                    'jumuiya_id' => $get('jumuiya_id') ?? Null,
                                    'received_confirmation' => $get('received_confirmation')  ?? Null,
                                    'confirmation_place' => $get('confirmation_place')  ?? Null,
                                    'confirmation_date' => $get('confirmation_date')  ?? Null,
                                    'received_baptism' => $get('received_baptism')  ?? Null,
                                    'baptism_place' => $get('baptism_place') ?? Null,
                                    'baptism_date' => $get('baptism_date')  ?? Null,
                                    'volunteering_in' => $get('volunteering_in')  ?? Null,
                                    'sacrament_participation' => $get('sacrament_participation') ?? Null,
                                    'previous_church' => $get('previous_church') ?? Null,
                                    'education_level' => $get('education_level') ?? Null,
                                    'profession' => $get('profession') ?? Null,
                                    'skills' => $get('skills') ?? Null,
                                    // 'work_location' => $get('work_location') ?? Null,
                                    'spiritual_information' => is_null($get('received_baptism') ? Null : 'complete'),
                                    'user_id' => auth()->user()->id,
                                    'church_id' => auth()->user()->church_id
                                ]);
                            }
                            // else{
                            //     $church_member = new ChurchMember;
                            //     $church_member->first_name = $get('first_name');
                            //     $church_member->middle_name = $get('middle_name');
                            //     $church_member->surname = $get('surname');
                            //     $church_member->email = $get('email') ?? Null;
                            //     $church_member->phone = $get('phone');
                            //     $church_member->gender = $get('gender');
                            //     $church_member->marital_status = $get('marital_status');
                            //     $church_member->date_of_birth = $get('date_of_birth');
                            //     $church_member->citizenship = $get('citizenship');
                            //     $church_member->spouse_first_name = $get('spouse_first_name');
                            //     $church_member->spouse_middle_name = $get('spouse_middle_name');
                            //     $church_member->spouse_surname = $get('spouse_surname');
                            //     $church_member->spouse_residence_status = $get('spouse_residence_status');
                            //     $church_member->spouse_contact_no = $get('spouse_contact_no');
                            //     $church_member->personal_details = is_null($get('first_name')) ? Null : 'complete';
                            //     $church_member->nida_id = $get('nida_id') ?? Null;
                            //     $church_member->passport_id = $get('passport_id') ?? Null;
                            //     $church_member->picture = $get('picture') ?? Null;
                            //     $church_member->postal_code = $get('postal_code') ?? Null;
                            //     $church_member->region_id = $get('region_id') ?? Null;
                            //     $church_member->district_id = $get('district_id') ?? Null;
                            //     $church_member->ward_id = $get('ward_id')?? Null;
                            //     $church_member->street = $get('street') ?? Null;
                            //     $church_member->block_no = $get('block_no') ?? Null;
                            //     $church_member->house_no = $get('house_no') ?? Null;
                            //     $church_member->address_details = is_null($get('region_id')) ? Null : 'complete';
                            //     $church_member->jumuiya_id = $get('jumuiya_id') ?? Null;
                            //     $church_member->received_confirmation = $get('received_confirmation') ?? Null;
                            //     $church_member->confirmation_place = $get('confirmation_place') ?? Null;
                            //     $church_member->confirmation_date = $get('confirmation_date') ?? Null;
                            //     $church_member->received_baptism = $get('received_baptism') ?? Null;
                            //     $church_member->baptism_place = $get('baptism_place') ?? Null;
                            //     $church_member->baptism_date = $get('baptism_date') ?? Null;
                            //     $church_member->volunteering_in = $get('volunteering_in') ?? Null;
                            //     $church_member->sacrament_participation = $get('sacrament_participation') ?? Null;
                            //     $church_member->previous_church = $get('previous_church') ?? Null;
                            //     $church_member->education_level = $get('education_level')?? Null;
                            //     $church_member->profession = $get('profession') ?? Null;
                            //     $church_member->skills = $get('skills') ?? Null;
                            //     $church_member->work_location = $get('work_location') ?? Null;
                            //     $church_member->spiritual_information = is_null($get('received_baptism')) ? Null : 'complete';
                            //     $church_member->user_id = auth()->user()->id;
                            //     $church_member->church_id = auth()->user()->church_id;
                            //     $church_member->save();
                            // }
                        }),

                        Step::make('Annual Pledges')
                            ->schema([

                                Checkbox::make('is_NewMember')
                                    ->label('Is NewMember?')
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
                                                ->options(Card::where('church_id', auth()->user()->church_id)->where('card_status', 'active')->pluck('card_name', 'id')->toArray())
                                                ->searchable()
                                                // ->default(function(Repeater $component){
                                                //     $cards = Card::where('church_id', auth()->user()->church_id)->where('card_status', 'active')->pluck('card_name', 'id')->toArray();
                                                //     if($cards->count() > 0){
                                                //         $componentState = $component->getState();
                                                //     }
                                                // })
                                                ->distinct(),

                                            TextInput::make('amount_pledged')
                                                ->numeric(),

                                        ])
                                        ->columnSpan('full')
                                        ->columns(2)
                                        ->addable(false)
                                        ->deletable(false)
                                        ->defaultItems(function(){
                                            $cards = Card::where('church_id', auth()->user()->church_id)->where('card_status', 'active')->count();
                                            return $cards ?? 0;
                                        }),
                            ])
                            ->afterValidation(function (Get $get) {
                                if(ChurchMember::where('user_id', auth()->user()->id)->count() > 0){
                                    $church_member = ChurchMember::where('user_id', auth()->user()->id)->first();
                                    $church_member->update([
                                        // 'first_name' => $get('first_name'),
                                        // 'middle_name' => $get('middle_name'),
                                        // 'surname' => $get('surname'),
                                        // 'email' => $get('email') ?? Null,
                                        // 'phone' => $get('phone'),
                                        // 'gender' => $get('gender'),
                                        // 'marital_status' => $get('marital_status'),
                                        // 'date_of_birth' => $get('date_of_birth'),
                                        // 'citizenship' => $get('citizenship'),
                                        // 'spouse_first_name' => $get('spouse_first_name') ?? Null,
                                        // 'spouse_middle_name' => $get('spouse_middle_name') ?? Null,
                                        // 'spouse_surname' => $get('spouse_surname') ?? Null,
                                        // 'spouse_citizenship' => $get('spouse_citizenship') ?? Null,
                                        // 'spouse_residence_status' => $get('spouse_residence_status') ?? Null,
                                        // 'spouse_contact_no' => $get('spouse_contact_no') ?? Null,
                                        // 'personal_details' => is_null($get('first_name')) ? Null : 'complete',
                                        // 'nida_id' => $get('nida_id') ?? Null,
                                        // 'passport_id' => $get('passport_id') ?? Null,
                                        // 'picture' => $get('picture') ?? Null,
                                        // 'postal_code' => $get('postal_code') ?? Null,
                                        // 'region_id' => $get('region_id') ?? Null,
                                        // 'district_id' => $get('district_id') ?? Null,
                                        // 'ward_id' => $get('ward_id') ?? Null,
                                        // 'street' => $get('street') ?? Null,$get('street'),
                                        // 'block_no' => $get('block_no') ?? Null,
                                        // 'house_no' => $get('house_no') ?? Null,
                                        // 'address_details' => (is_null($get('region_id')) && auth()->user()->residence_status == 'Resident') ? Null : ((is_null($get('resident_country')) && auth()->user()->residence_status == 'Non Resident') ? Null : 'complete'),
                                        // 'jumuiya_id' => $get('jumuiya_id') ?? Null,
                                        // 'received_confirmation' => $get('received_confirmation')  ?? Null,
                                        // 'confirmation_place' => $get('confirmation_place')  ?? Null,
                                        // 'confirmation_date' => $get('confirmation_date')  ?? Null,
                                        // 'received_baptism' => $get('received_baptism')  ?? Null,
                                        // 'baptism_place' => $get('baptism_place') ?? Null,
                                        // 'baptism_date' => $get('baptism_date')  ?? Null,
                                        // 'volunteering_in' => $get('volunteering_in')  ?? Null,
                                        // 'sacrament_participation' => $get('sacrament_participation') ?? Null,
                                        // 'previous_church' => $get('previous_church') ?? Null,
                                        // 'education_level' => $get('education_level') ?? Null,
                                        // 'profession' => $get('profession') ?? Null,
                                        // 'skills' => $get('skills') ?? Null,
                                        // 'work_location' => $get('work_location') ?? Null,
                                        // 'spiritual_information' => is_null($get('received_baptism') ? Null : 'complete'),
                                        'is_NewMember' => $get('is_NewMember') ?? Null,
                                        'card_no' => $get('card_no') ?? Null,
                                        'user_id' => auth()->user()->id,
                                        'church_id' => auth()->user()->church_id
                                    ]);

                                    //add card pledges
                                    if(count($get('Card Pledges')) > 0){
                                        CardPledge::where('church_member_id', $church_member->id)->delete();

                                        foreach($get('Card Pledges') as $pledge){
                                            if($pledge['card_type'] != Null && $pledge['amount_pledged'] != Null){

                                                $card_pledge = new CardPledge;
                                                $card_pledge->church_member_id = $church_member->id;
                                                $card_pledge->card_id = $pledge['card_type'];
                                                $card_pledge->card_no = $this->form->getState()['card_no'];
                                                $card_pledge->amount_pledged = $pledge['amount_pledged'];
                                                $card_pledge->amount_completed = 0;
                                                $card_pledge->date_pledged = $church_member->created_at;
                                                $card_pledge->created_by = auth()->user()->id;
                                                $card_pledge->church_id = auth()->user()->church_id;
                                                $card_pledge->status = 'Active';
                                                $card_pledge->save();
                                            }
                                        }
                                    }


                                }
                                // else{
                                //     $church_member = new ChurchMember;
                                //     $church_member->first_name = $get('first_name');
                                //     $church_member->middle_name = $get('middle_name');
                                //     $church_member->surname = $get('surname');
                                //     $church_member->email = $get('email') ?? Null;
                                //     $church_member->phone = $get('phone');
                                //     $church_member->gender = $get('gender');
                                //     $church_member->marital_status = $get('marital_status');
                                //     $church_member->date_of_birth = $get('date_of_birth');
                                //     $church_member->citizenship = $get('citizenship');
                                //     $church_member->spouse_first_name = $get('spouse_first_name');
                                //     $church_member->spouse_middle_name = $get('spouse_middle_name');
                                //     $church_member->spouse_surname = $get('spouse_surname');
                                //     $church_member->spouse_residence_status = $get('spouse_residence_status');
                                //     $church_member->spouse_contact_no = $get('spouse_contact_no');
                                //     $church_member->personal_details = is_null($get('first_name')) ? Null : 'complete';
                                //     $church_member->nida_id = $get('nida_id') ?? Null;
                                //     $church_member->passport_id = $get('passport_id') ?? Null;
                                //     $church_member->picture = $get('picture') ?? Null;
                                //     $church_member->postal_code = $get('postal_code') ?? Null;
                                //     $church_member->region_id = $get('region_id') ?? Null;
                                //     $church_member->district_id = $get('district_id') ?? Null;
                                //     $church_member->ward_id = $get('ward_id')?? Null;
                                //     $church_member->street = $get('street') ?? Null;
                                //     $church_member->block_no = $get('block_no') ?? Null;
                                //     $church_member->house_no = $get('house_no') ?? Null;
                                //     $church_member->address_details = is_null($get('region_id')) ? Null : 'complete';
                                //     $church_member->jumuiya_id = $get('jumuiya_id') ?? Null;
                                //     $church_member->received_confirmation = $get('received_confirmation') ?? Null;
                                //     $church_member->confirmation_place = $get('confirmation_place') ?? Null;
                                //     $church_member->confirmation_date = $get('confirmation_date') ?? Null;
                                //     $church_member->received_baptism = $get('received_baptism') ?? Null;
                                //     $church_member->baptism_place = $get('baptism_place') ?? Null;
                                //     $church_member->baptism_date = $get('baptism_date') ?? Null;
                                //     $church_member->volunteering_in = $get('volunteering_in') ?? Null;
                                //     $church_member->sacrament_participation = $get('sacrament_participation') ?? Null;
                                //     $church_member->previous_church = $get('previous_church') ?? Null;
                                //     $church_member->education_level = $get('education_level')?? Null;
                                //     $church_member->profession = $get('profession') ?? Null;
                                //     $church_member->skills = $get('skills') ?? Null;
                                //     $church_member->work_location = $get('work_location') ?? Null;
                                //     $church_member->spiritual_information = is_null($get('received_baptism')) ? Null : 'complete';
                                //     $church_member->is_NewMember = $get('is_NewMember') ?? Null;
                                //     $church_member->card_no = $get('card_no') ?? Null;
                                //     $church_member->user_id = auth()->user()->id;
                                //     $church_member->church_id = auth()->user()->church_id;
                                //     $church_member->save();

                                //     //add card pledges
                                //     if(count($get('Card Pledges')) > 0){
                                //         foreach($get('Card Pledges') as $pledge){
                                //             if($pledge['card_type'] != Null && $pledge['amount_pledged'] != Null){
                                //                 $card_pledge = new CardPledge;
                                //                 $card_pledge->church_member_id = $church_member->id;
                                //                 $card_pledge->card_id = $pledge['card_type'];
                                //                 $card_pledge->card_no = $this->form->getState()['card_no'];
                                //                 $card_pledge->amount_pledged = $pledge['amount_pledged'];
                                //                 $card_pledge->amount_completed = 0;
                                //                 $card_pledge->date_pledged = $church_member->created_at;
                                //                 $card_pledge->created_by = auth()->user()->id;
                                //                 $card_pledge->church_id = auth()->user()->church_id;
                                //                 $card_pledge->status = 'Active';
                                //                 $card_pledge->save();
                                //             }
                                //         }
                                //     }

                                // }
                            }),


                        ])
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                            <x-filament::button
                                type="submit"
                                size="sm"
                            >
                                Submit
                            </x-filament::button>
                        BLADE))),
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
                'citizenship' => $this->form->getState()['citizenship'],
                'marital_status' => $this->form->getState()['marital_status'],
                'date_of_birth' => $this->form->getState()['date_of_birth'],
                'spouse_first_name' => $this->form->getState()['spouse_first_name'] ?? Null,
                'spouse_middle_name' => $this->form->getState()['spouse_middle_name'] ?? Null,
                'spouse_surname' => $this->form->getState()['spouse_surname'] ?? Null,
                'spouse_citizenship' => $this->form->getState()['spouse_citizenship'] ?? Null,
                'spouse_residence_status' => $this->form->getState()['spouse_residence_status'] ?? Null,
                'picture' => $this->form->getState()['picture'] ?? Null,
                'personal_details' => is_null($this->form->getState()['first_name']) ? Null : 'complete',
                'nida_id' => $this->form->getState()['nida_id'] ?? Null,
                'passport_id' => $this->form->getState()['passport_id'] ?? Null,
                'driver_id' => $this->form->getState()['driver_id'] ?? Null,
                'postal_code' => $this->form->getState()['postal_code'] ?? Null,
                'region_id' => $this->form->getState()['region_id'] ?? Null,
                'district_id' => $this->form->getState()['district_id'] ?? Null,
                'ward_id' => $this->form->getState()['ward_id'] ?? Null,
                'street' => $this->form->getState()['street'] ?? Null,
                'block_no' => $this->form->getState()['block_no'] ?? Null,
                'house_no' => $this->form->getState()['house_no'] ?? Null,
                'id_image' => $this->form->getState()['id_image'] ?? Null,
                'resident_country' => $this->form->getState()['resident_country'] ?? Null,
                'resident_city' => $this->form->getState()['resident_city'] ?? Null,
                'resident_street' => $this->form->getState()['resident_street'] ?? Null,
                'address_details' => auth()->user()->residence_status == 'Resident' ? (is_null($this->form->getState()['region_id']) ? Null : 'complete') : (is_null($this->form->getState()['resident_country']) ? Null : 'complete'),
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

            $dependants =  $this->form->getState()['has_dependants'] ? $this->form->getState()['dependants'] : Null;
            if($dependants != Null){
                if(Dependant::where('church_member_id', $church_member->id)->exists()){
                    //delete all dependants enter new data
                    Dependant::where('church_member_id', $church_member->id)->delete();

                    foreach($dependants as $dependant){
                        $member_dependants = new Dependant;
                        $member_dependants->first_name = $dependant['first_name'];
                        $member_dependants->middle_name = $dependant['middle_name'];
                        $member_dependants->surname = $dependant['surname'];
                        $member_dependants->gender = $dependant['gender'];
                        $member_dependants->date_of_birth = $dependant['date_of_birth'];
                        $member_dependants->relationship = $dependant['relationship'];
                        $member_dependants->church_member_id = $church_member->id;
                        $member_dependants->save();
                    }

                }
            }

        }else{
            $church_member = new ChurchMember;
            $church_member->first_name = $this->form->getState()['first_name'];
            $church_member->middle_name = $this->form->getState()['middle_name'];
            $church_member->surname = $this->form->getState()['surname'];
            $church_member->email = $this->form->getState()['email'] ?? Null;
            $church_member->phone = $this->form->getState()['phone'];
            $church_member->gender = $this->form->getState()['gender'];
            $church_member->citizenship = $this->form->getState()['citizenship'];
            $church_member->marital_status = $this->form->getState()['marital_status'];
            $church_member->date_of_birth = $this->form->getState()['date_of_birth'];
            $church_member->nida_id = $this->form->getState()['nida_id'] ?? Null;
            $church_member->passport_id = $this->form->getState()['passport_id'] ?? Null;
            $church_member->driver_id = $this->form->getState()['driver_id'] ?? Null;
            $church_member->picture = $this->form->getState()['picture'] ?? Null;
            $church_member->spouse_first_name = $this->form->getState()['spouse_first_name'] ?? Null;
            $church_member->spouse_middle_name = $this->form->getState()['spouse_middle_name'] ?? Null;
            $church_member->spouse_surname = $this->form->getState()['spouse_surname'] ?? Null;
            $church_member->spouse_citizenship = $this->form->getState()['spouse_citizenship'] ?? Null;
            $church_member->spouse_residence_status = $this->form->getState()['spouse_residence_status'] ?? Null;
            $church_member->personal_details = is_null($this->form->getState()['first_name']) ? Null : 'complete';
            $church_member->postal_code = $this->form->getState()['postal_code'] ?? Null;
            $church_member->region_id = $this->form->getState()['region_id'] ?? Null;
            $church_member->district_id = $this->form->getState()['district_id'] ?? Null;
            $church_member->ward_id = $this->form->getState()['ward_id'] ?? Null;
            $church_member->street = $this->form->getState()['street'] ?? Null;
            $church_member->block_no = $this->form->getState()['block_no'] ?? Null;
            $church_member->house_no = $this->form->getState()['house_no'] ?? Null;
            $church_member->resident_country = $this->form->getState()['resident_country'] ?? Null;
            $church_member->resident_city = $this->form->getState()['resident_city'] ?? Null;
            $church_member->resident_street = $this->form->getState()['resident_street'] ?? Null;
            $church_member->id_image = $this->form->getState()['id_image'] ?? Null;
            $church_member->address_details = auth()->user()->residence_status == 'Resident' ? (is_null($this->form->getState()['region_id']) ? Null : 'complete') : (is_null($this->form->getState()['resident_country']) ? Null : 'complete');
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

        $dependants =  $this->form->getState()['has_dependants'] ? $this->form->getState()['dependants'] : Null;
        if($dependants != Null){
            foreach($dependants as $dependant){
                $member_dependants = new Dependant;
                $member_dependants->first_name = $dependant['first_name'];
                $member_dependants->middle_name = $dependant['middle_name'];
                $member_dependants->surname = $dependant['surname'];
                $member_dependants->gender = $dependant['gender'];
                $member_dependants->date_of_birth = $dependant['date_of_birth'];
                $member_dependants->relationship = $dependant['relationship'];
                $member_dependants->church_member_id = $church_member->id;
                $member_dependants->save();
            }
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
