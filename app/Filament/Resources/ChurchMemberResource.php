<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchMemberResource\Pages;
use App\Filament\Resources\ChurchMemberResource\RelationManagers;
use App\Models\ChurchMember;
use App\Models\Church;
use App\Models\Card;
use Filament\Forms;
use App\Models\Region;
use App\Models\District;
use App\Models\Jumuiya;
use App\Models\Ward;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
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
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Validation\Rules\Unique;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Grid as ViewGrid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as ViewSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\Radio;

class ChurchMemberResource extends Resource
{
    protected static ?string $model = ChurchMember::class;

    protected static ?string $navigationIcon = 'fas-person';

    protected static ?string $navigationGroup = 'Church Administration';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any ChurchMember');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Member Person Details')
                            ->schema([
                                ViewGrid::make(4)
                                    ->schema([
                                        TextEntry::make('first_name'),
                                        TextEntry::make('middle_name'),
                                        TextEntry::make('surname'),
                                        TextEntry::make('email')
                                            ->placeholder('No e-mail provided'),
                                        TextEntry::make('gender'),
                                        TextEntry::make('phone'),
                                        TextEntry::make('citizenship'),
                                        TextEntry::make('marital_status'),
                                        TextEntry::make('date_of_birth')
                                            ->date(),     
                                        ImageEntry::make('picture')
                                            ->label('Member Picture')
                                            ->height(50)
                                            ->circular(),                                  
                                    ]),

                                ViewSection::make('Marital Status Info')
                                    // ->description(fn($record) => $record->marital_status == 'Married' ? 'Spouse Information' : 'No Spouse Information Provided')
                                    ->columns(2)
                                    ->schema([
                                        TextEntry::make('spouse_name')
                                            ->hidden(fn($state) => blank($state) ? true : false),
                                        TextEntry::make('spouse_contact_no')
                                            ->hidden(fn($state) => blank($state) ? true : false),
                                    ])
                                    ->hidden(fn($record) => blank($record) ? true : ($record->marital_status == 'Married' ? false : true)),

                                RepeatableEntry::make('dependants')
                                    ->schema([
                                        ViewGrid::make(4)
                                            ->schema([
                                                TextEntry::make('first_name'),
                                                TextEntry::make('middle_name'),
                                                TextEntry::make('surname'),
                                                TextEntry::make('date_of_birth')
                                                    ->date(),
                                                TextEntry::make('gender'),
                                                TextEntry::make('relationship')
                                            ])
                                    ])
                                
                            ]),

                        Tabs\Tab::make('Identifications & Physical Address Details')
                            ->schema([
                                Split::make([
                                    ViewSection::make([
                                        TextEntry::make('identification_type')
                                            ->label('ID Type')
                                            ->state(function(Model $record){
                                                if($record->nida_id != Null){
                                                    return 'nida';
                                                }else{
                                                    return 'passport';
                                                }
                                            }),
                                        TextEntry::make('nida_id')
                                            ->label('ID Number')
                                            ->visible(function($record){
                                                if($record->nida_id != Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                        TextEntry::make('passport_id')
                                            ->label('Passport ID Number')
                                            ->visible(function($record){
                                                if($record->passport != Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                        TextEntry::make('driver_id')
                                            ->label('Driving License ID Number')
                                            ->visible(function($record){
                                                if($record->passport != Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                        ImageEntry::make('id_image')
                                            ->label('ID Image')
                                            ->height(50)
                                            ->circular(),
                                        ])
                                        ->description('Identification Details')
                                        ->columns(3),

                                    ViewSection::make([
                                        TextEntry::make('postal_code')
                                            ->hidden(fn($record) => $record->postal_code == Null ? true : false),
                                        TextEntry::make('region_id')
                                            ->label('Region')
                                            ->state(function(Model $record){
                                                return Region::whereId($record->region_id)->pluck('name');
                                            })
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),
                                        TextEntry::make('district_id')
                                            ->label('District')
                                            ->state(function(Model $record){
                                                return District::whereId($record->district_id)->pluck('name');
                                            })
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),
                                        TextEntry::make('ward_id')
                                            ->label('Ward')
                                            ->state(function(Model $record){
                                                return Ward::whereId($record->ward_id)->pluck('name');
                                            })
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),
                                        TextEntry::make('street')
                                            ->placeholder('No Street Information provided')
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),
                                        TextEntry::make('block_no')
                                            ->placeholder('No Block No provided')
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),                                      
                                        TextEntry::make('house_no')
                                            ->placeholder('No House No provided')
                                            ->hidden(fn() => auth()->user()->residence_status == 'Non Resident' ? true : false),

                                        TextEntry::make('resident_country')
                                            ->label('Resident Country')
                                            ->state(function(Model $record){
                                                return Country::whereId('code', $record->resident_country)->pluck('name');
                                            })
                                            ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),
                                        TextEntry::make('resident_city')
                                            ->label('Resident City')
                                            ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),
                                        TextEntry::make('resident_street')
                                            ->label('Resident Street')
                                            ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),
                                    ])
                                    ->description('Address Details')
                                    ->columns(3)
                                ])
                            ])
                            ->visible(function(Model $record){
                                if($record->address_details == 'complete'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        Tabs\Tab::make('Other Information')
                            ->schema([
                                ViewSection::make([
                                    TextEntry::make('jumuiya_id')
                                        ->label('Communities')
                                        ->state(function(Model $record){
                                            return Jumuiya::whereId($record->jumiya_id)->pluck('name');
                                        })
                                        ->placeholder('No Jumuiya Specified'),
                                    TextEntry::make('jumuiya_location')
                                        ->state(function(Model $record){
                                            return Jumuiya::whereId($record->jumuiya_id)->pluck('ward');
                                        })
                                        ->placeholder('No Jumuiya Specified')

                                ])
                                ->columns(2)
                                ->description('Details of Church Communities')
                                ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),

                                Split::make([
                                    ViewSection::make([
                                        IconEntry::make('received_confirmation')
                                            ->boolean()
                                            ->trueColor('success')
                                            ->falseColor('warning'),
                                        TextEntry::make('confirmation_place')
                                            ->placeholder('Confimrmation Place Not Specified'),
                                        TextEntry::make('confirmation_date')
                                            ->placeholder('Confirmation Date Not Specified')
                                    ])
                                    ->columns(3)
                                    ->description('Confirmation Details'),

                                    ViewSection::make([
                                        IconEntry::make('received_baptism')
                                            ->boolean()
                                            ->trueColor('success')
                                            ->falseColor('warning'),
                                        TextEntry::make('baptism_place')
                                            ->placeholder('Baptism Place Not Specified'),
                                        TextEntry::make('baptism_date')
                                            ->date()
                                            ->placeholder('Baptism Date Not Specified')
                                    ])
                                    ->columns(3)
                                    ->description('Baptism Details')
                                    ]),

                                ViewSection::make([
                                    TextEntry::make('volunteering_in')
                                        ->placeholder('No Volunteering Requested')
                                        ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),
                                    TextEntry::make('sacrament_participation')
                                        ->placeholder('No Information provided')
                                        ->hidden(fn() => auth()->user()->residence_status == 'Resident' ? true : false),
                                    TextEntry::make('previous_church')
                                    ->placeholder('No Information provided'),
                                ])
                                ->columns(3)
                                ->description('Other Details'),

                                ViewSection::make([
                                    TextEntry::make('education_level')
                                        ->placeholder('No Information Provided'),
                                    TextEntry::make('profession')
                                        ->placeholder('No Information Provided'),
                                    TextEntry::make('skills')
                                        ->placeholder('No Information Provided'),
                                    TextEntry::make('work_location')
                                        ->placeholder('No information Provided')
                                        ->hidden()
                                ])
                                ->columns(4)
                                ->description('Education And Professional Information')
                                ])
                                ->visible(function(Model $record){
                                    if($record->address_details == 'complete' && $record->spiritual_information == 'complete'){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }),

                        Tabs\Tab::make('Member Card Details')
                            ->schema([
                                ViewSection::make([
                                    IconEntry::make('is_NewMember')
                                        ->boolean()
                                        ->trueColor('success')
                                        ->falseColor('warning'),

                                    TextEntry::make('card_no')
                                        ->placeholder('New Member Has No Card No')
                                ])
                                ->columns(2)
                                ->description('Member Card Status Information'),

                                RepeatableEntry::make('pledges')
                                    ->schema([
                                        TextEntry::make('card.card_name')
                                            ->placeholder('No Pledge Made By Member'),
                                        TextEntry::make('amount_pledged')
                                            ->numeric(
                                                decimalPlaces: 0,
                                                decimalSeparator: '.',
                                                thousandsSeparator: ',',
                                            )
                                            ->placeholder('No Amount Pledged By Member')
                                    ])
                                    ->grid(2)
                                    ->columns(2)
                                    ->hidden(fn(Model $record) => $record->pledges ? false : true)
                                    ->description('Annual Pledges'),

                            ])
                            ->visible(function(Model $record){
                                if($record->personal_details == 'complete' && $record->address_details == 'complete' && $record->spiritual_information == 'complete'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                    ])
            ]);

    }        
    public static function form(Form $form): Form
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

                                Select::make('residence_status')
                                    ->label('Residential Status')
                                    ->live()
                                    ->options([
                                        'Resident' => 'Resident',
                                        'Non Resident' => 'Non Resident'
                                    ])
                                    ->required(),

                                TextInput::make('phone')
                                    ->tel()
                                    ->helperText(function(Get $get){
                                        if($get('residence_status') == 'Resident'){
                                            return '0789******';
                                        }else if($get('residence_status') == 'Non Resident'){
                                            return '+1234xxxxxxx';
                                        }else{
                                            return '0789******';
                                        }
                                   })
                                   ->maxLength(fn() => auth()->user()->residence_status == 'Resident' ? 10 : 20)
                                   ->required(),

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
                                    ->minDate(function(string $state){
                                        $dob = Carbon::parse($state);
                                        return $dob->addYears(12);
                                    })
                                    ->maxDate(Carbon::now()->subDay()),


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
                                        // Checkbox::make('is_member')
                                        //     ->default(true)
                                        //     ->reactive(),
                                        // Select::make('spouse_id')
                                        //     ->searchable()
                                        //     ->options(function(){
                                        //         return ChurchMember::where('church_id', auth()->user()->church_id)->where('gender', 'Female')->pluck('full_name', 'id');
                                        //     })
                                        //     ->visible(fn(Get $get) => $get('is_member') == true ? true : false),
                                        // TextInput::make('spouse_name')
                                        //     ->required()
                                        //     ->visible(fn(Get $get) => $get('is_member') == false ? true : false),

                                        // TextInput::make('spouse_contact_no')
                                        //     ->tel()
                                        //     ->maxLength(13)
                                        //     ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/')
                                        //     ->helperText('+255*********')
                                        //     ->required(),

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
                                        ->required()
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
                                            ->maxLength(fn() => auth()->user()->residence_status == 'Resident' ? 10 : 20)
                                            ->required(),

                                    ])
                                    ->columns(3)
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

                                ]),

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
                                            ->options(Region::pluck('name', 'id')->toArray())
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
                                            ->visible(function(Get $get){
                                                if($get('residence_status') == 'Resident'){
                                                    return false;
                                                }else if($get('residence_status') == 'Non Resident'){
                                                    return true;
                                                }else{
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
                                            ->visible(function(Get $get){
                                                if($get('residence_status') == 'Resident'){
                                                    return false;
                                                }else if($get('residence_status') == 'Non Resident'){
                                                    return true;
                                                }else{
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
                                            ->visible(function(Get $get){
                                                if($get('residence_status') == 'Resident'){
                                                    return false;
                                                }else if($get('residence_status') == 'Non Resident'){
                                                    return true;
                                                }else{
                                                    return true;
                                                }
                                            }),
    
                                            TextInput::make('street')
                                                ->nullable()
                                                ->visible(function(Get $get){
                                                    if($get('residence_status') == 'Resident'){
                                                        return false;
                                                    }else if($get('residence_status') == 'Non Resident'){
                                                        return true;
                                                    }else{
                                                        return true;
                                                    }
                                                }),
    
                                            TextInput::make('block_no')
                                                ->nullable()
                                                ->visible(function(Get $get){
                                                    if($get('residence_status') == 'Resident'){
                                                        return false;
                                                    }else if($get('residence_status') == 'Non Resident'){
                                                        return true;
                                                    }else{
                                                        return true;
                                                    }
                                                }),
    
                                            TextInput::make('house_no')
                                                ->nullable()
                                                ->visible(function(Get $get){
                                                    if($get('residence_status') == 'Resident'){
                                                        return false;
                                                    }else if($get('residence_status') == 'Non Resident'){
                                                        return true;
                                                    }else{
                                                        return true;
                                                    }
                                                }),
    
                                            Select::make('resident_country')
                                                ->label('Resident Country')
                                                ->options(Country::all()->pluck('name','code'))
                                                ->required()
                                                ->visible(function(Get $get){
                                                    if($get('residence_status') == 'Non Resident'){
                                                        return true;
                                                    }else if($get('residence_status') == 'Resident'){
                                                        return false;
                                                    }else{
                                                        return false;
                                                    }
                                                }),
    
                                            TextInput::make('resident_city')
                                            ->label('Resident City')
                                            ->required()
                                            ->visible(function(){
                                                if($get('residence_status') == 'Non Resident'){
                                                    return true;
                                                }else if($get('residence_status') == 'Resident'){
                                                    return false;
                                                }else{
                                                    return false;
                                                }
                                            }),
    
                                            TextInput::make('resident_street')
                                            ->label('Resident Street')
                                            ->nullable()
                                            ->visible(function(){
                                                if($get('residence_status') == 'Non Resident'){
                                                    return true;
                                                }else if($get('residence_status') == 'Resident'){
                                                    return false;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                                
                                        ]),
    
                                    ]),


                        Step::make('ID Details')
                            ->columns(2)
                            ->schema([
                                Select::make('identification_type')
                                    ->label('ID Type')
                                    ->options(function (Get $get) {
                                        if($get('residence_status') == 'Non Resident'){
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
                                    ->placeHolder('eg. xxxxxxxx-xxxxx-xxxxx-xx')
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
                                    ->downloadable()
                                    ->nullable()
                                    ->columnSpan('full'),

                                ]),

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
                                    ])
                                    ->hidden(function(Get $get){
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
                                            ->visible(function(Get $get){
                                                if($get('residence_status') == 'Non Resident'){
                                                    return false;
                                                }else if($get('residence_status') == 'Resident'){
                                                    return true;
                                                }else{
                                                    return true;
                                                }
                                            }),

                                        Select::make('sacrament_participation')
                                            ->required()
                                            ->options([
                                                'yes' => 'Yes',
                                                'no' => 'No'
                                            ])
                                            ->visible(function(Get $get){
                                                if($get('residence_status') == 'Non Resident'){
                                                    return false;
                                                }else if($get('residence_status') == 'Resident'){
                                                    return true;
                                                }else{
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
                        ]),

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
                                                ->options(Card::where('church_id', auth()->user()->church_id)->pluck('card_name', 'id')->toArray())
                                                ->searchable()
                                                // ->required(function(string $context){
                                                //         if(auth()->user()->churchMember){
                                                //             if(auth()->user()->churchMember->spiritual_information !== Null){
                                                //                 return true;
                                                //             }else{
                                                //                 return false;
                                                //             }
                                                //         }else{
                                                //             return false;
                                                //         }
                                                // })
                                                ->distinct(),

                                            TextInput::make('amount_pledged')
                                                ->numeric(),
                                                // ->required(function(string $context){
                                                //         if(auth()->user()->churchMember){
                                                //             if(auth()->user()->churchMember->spiritual_information !== Null){
                                                //                 return true;
                                                //             }else{
                                                //                 return false;
                                                //             }
                                                //         }else{
                                                //             return false;
                                                //         }
                                                // })

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


                ])
                ->startOnStep(function($record, string $context){
                    if($context == 'create'){
                        return 1;
                    }else{
                        if($record->spiritual_information == 'complete'){
                            return 5;
                        }else{
                            if($record->address_details == 'complete'){
                                return 4;
                            }else{
                                if($record->personal_details == 'complete'){
                                    return 2;
                                }else{
                                    return 1;
                                }
                            }
                        }
                    }
                })->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            Submit
                        </x-filament::button>
                    BLADE))),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->default(function($record){
                        return "{$record->surname}, {$record->first_name} {$record->middle_name}";
                    }),
                TextColumn::make('phone'),
                TextColumn::make('marital_status'),
                TextColumn::make('church_id')
                    ->label('Church')
                    ->formatStateUsing(function($record){
                        return Church::whereId($record->church_id)->pluck('name')[0];
                    }),
                TextColumn::make('card_no')
                    ->default(function($record){
                        if(blank($record->card_no)){
                            return 'New Member';
                        }else{
                            return $record->card_no;
                        }
                    })
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->hidden(auth()->user()->checkPermissionTo('update ChurchMember')),
                Tables\Actions\ViewAction::make()
                ->hidden(auth()->user()->checkPermissionTo('view ChurchMember')),
                Action::make('approve')
                    ->label(function($record){
                        if($record->comment != Null && $record->status == 'active'){
                            return 'Physically approved';
                        }else{
                            return 'Physical Approve';
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
                        
                        Radio::make('status')
                        ->options([
                            'active' => 'Approve',
                            'inactive' => 'Not Approved'
                        ])
                        ->inline(),

                        TextInput::make('comment')
                            ->visible(function(Get $get){
                                if($get('status') == 'inactive'){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->required(),
                    ])
                    ->action(function(array $data, ChurchMember $record): void{
                        $record->comment = $data['comment'] ?? Null;
                        $record->status = $data['status'];
                        $record->physically_approved_by = auth()->user()->id;
                        $record->date_registered = now();
                        $record->save();

                        if(! User::wherePhone($this->record->phone)->exists()){
                            $user = new User;
                            $user->phone = $this->record->phone;
                            $user->password = Hash::make($this->record->phone);
                            $user->residence_status = $this->record->citizenship !== Null ? 'Non Resident' : 'Resident';
                            $user->dinomination_id = auth()->user()->dinomination_id;
                            $user->church_id = auth()->user()->church_id;
                            $user->save();

                            $this->record->update([
                                'user_id' => $user->id
                            ]);
                        }

                        Notification::make()
                            ->title('Member successfully approved')
                            ->success()
                            ->send();
                    })
                    ->disabled(function($record){
                        if($record->comment != Null){
                            return true;
                        }else{
                            return false;
                        }
                    })
                    ->hidden(function(ChurchMember $record){
                        if($record->status == 'active'){
                            return true;
                        }else{
                            if(auth()->user()->checkPermissionTo('verify ChurchMember')){
                                return false;
                            }else{
                                return true;
                            }
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->visible(auth()->user()->checkPermissionTo('delete ChurchMember')),
                ]),
            ])
            ->emptyStateHeading('No pending memebership request');;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('church_id', auth()->user()->church_id)->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListChurchMembers::route('/'),
            'create' => Pages\CreateChurchMember::route('/create'),
            'edit' => Pages\EditChurchMember::route('/{record}/edit'),
            'view' => Pages\ViewChurchMember::route('/{record}'),       
        ];
    }
}
