<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\Grid as ViewGrid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section as ViewSection;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Split;
use App\Models\ChurchMember;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Jumuiya;
use App\Models\Card;
use App\Models\User;
use Filament\Pages\Actions\EditAction;
use Filament\Pages\Actions\Action;
use Filament\Pages\Actions\CreateAction;
use Filament\Support\Enums\Alignment;
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
use Filament\Support\Enums\MaxWidth;
use Illuminate\Validation\Rules\Unique;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class Members extends Page 
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.members';

    protected static ?string $navigationGroup = 'ChurchMember';

    protected static ?string $title = 'Member Details';


    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->churchMember){
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
        return Auth::guard('web')->user()->churchMember ? true : false;
    }

    public function getSubheading(): string 
    {
        $member = ChurchMember::where('user_id', auth()->user()->id)->first();

        if($member !== Null){
            if($member->personal_details == 'complete' && $member->address_details == 'complete' && $member->spiritual_information == 'complete'){
                if(ChurchMember::whereHas('pledges')->count() > 0){
                    if($member->status == 'active'){
                        return "Verified Member";
                    }else{
                        return "Registration Complete";
                    }
                }else{
                    return "You haven't pledged yet, Please enter card pledges";
                }
            }else{
                return "Please Complete Registration";
            }
        }
    }

    public static function getNavigationIcon(): ?string
    {
        return 'fas-person';
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     $member = ChurchMember::where('church_id', auth()->user()->church_id)->first();

    //     if($member !== Null){
    //         if($member->personal_details == 'complete' && $member->address_details == 'complete' && $member->spiritual_information == 'complete'){
    //             return 'Registered';
    //         }else{
    //             if($member->personal_details == 'complete'){
    //                 return 'Partial Registered';
    //             }else{
    //                 return 'Not Registered';
    //             }
    //         }
    //     }else{
    //         return 'Not Member';
    //     }
    // }

    /**
     * @return string | array{50: string, 100: string, 200: string, 300: string, 400: string, 500: string, 600: string, 700: string, 800: string, 900: string, 950: string} | null
     */
    // public static function getNavigationBadgeColor(): string | array | null
    // {
    //     if(static::getNavigationBadge() == 'Not Registered'){
    //         return 'danger';
    //     }else{
    //         if(static::getNavigationBadge() == 'Registered'){
    //             return 'success';
    //         }else{
    //             if(static::getNavigationBadge() == 'Partial Registered'){
    //                 return 'warning';
    //             }else if(static::getNavigationBadge() == 'Not Member'){
    //                 return 'danger';
    //             }
    //         }
    //     }
    // }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Edit Church Member')
                ->record(ChurchMember::where('user_id', auth()->user()->id)->first())
                ->form([
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
                                        ->tel()
                                        ->helperText('0789******')
                                        ->maxLength(10)
                                        ->required()
                                        ->default(User::whereId(auth()->id())->pluck('phone')[0])
                                        ->disabled(),

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
                                                ->telRegex('/^[+]*[(]{0,1}[0-9]{1,4}[)]{0,1}[\s\.\/0-9]*$/')
                                                ->helperText('+255*********')
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
                                        ->relationship('dependants')
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
                                    ]),

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

                                    ]),

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
                        ]),


                            Step::make('Card Pledge Information')
                                ->schema([

                                    Checkbox::make('is_NewMember')
                                        ->default(false)
                                        ->reactive()
                                        ->required(function(){
                                                if(auth()->user()->churchMember){
                                                    if(auth()->user()->churchMember->spiritual_information !== Null && auth()->user()->churchMember->address_details != Null){
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
                                                    if(auth()->user()->churchMember->spiritual_information !== Null && auth()->user()->churchMember->address_details != Null){
                                                        return true;
                                                    }
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
                                ]),



                    ])
                    ->startOnStep(function($record){
                            if($record->personal_details == 'complete' && $record->address_details != 'complete'){
                                return 2;
                            }else{
                                if($record->personal_details == 'complete' && $record->address_details == 'complete'){
                                    if($record->personal_details == 'complete' && $record->address_details == 'complete' && $record->spiritual_information == 'complete'){
                                        return 5;
                                    }else{
                                        return 4;

                                    }
                                }else{
                                    if($record->personal_details == 'complete' && $record->address_details == 'complete' && $record->spiritual_information == 'complete'){
                                        return 5;
                                    }else{
                                        return 1;
                                    }
                                }
                            }
                    }),
                ])
                ->slideOver()
                ->modalWidth(MaxWidth::SixExtraLarge)
                ->after(function (ChurchMember $record, array $data) {
                    if($record->first_name !== Null && $record->region_id !== Null && $record->received_confirmation !== Null){
                        $record->update([
                            'personal_details' => 'complete',
                            'address_details' => 'complete',
                            'spiritual_information' => 'complete'
                        ]);
                    }else{
                        if($record->first_name !== Null){
                            $record->update([
                                'personal_details' => 'complete'
                            ]);
                        }else{
                            if($record->region_id !== Null){
                                $record->update([
                                    'address_details' => 'complete'
                                ]);
                            }else{
                                if($data['received_confirmation'] !== Null){
                                    $record->update([
                                        'spiritual_information' => 'complete'
                                    ]);
                                }
                            }
                        }
                    }
                })
                ->visible(auth()->user()->churchMember()->count() > 0 ? true : false),
        ];
    }


    protected function makeInfolist(): Infolist
    {
        return Infolist::make($this)
                ->record(ChurchMember::where('user_id', auth()->user()->id)->first())
                ->schema([
                    Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Member Personal Details')
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
                                        TextEntry::make('marital_status'),
                                        TextEntry::make('date_of_birth')
                                            ->date(),                                       
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

                        Tabs\Tab::make('Identifications & Address Information')
                            ->schema([
                                Split::make([
                                    ViewSection::make([
                                        TextEntry::make('identification_type')
                                            ->state(function(Model $record){
                                                if($record->nida_id != Null){
                                                    return 'nida';
                                                }else{
                                                    return 'passport';
                                                }
                                            }),
                                        TextEntry::make('nida_id')
                                            ->visible(function($record){
                                                if($record->nida_id != Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                        TextEntry::make('passport_id')
                                            ->visible(function($record){
                                                if($record->passport != Null){
                                                    return true;
                                                }else{
                                                    return false;
                                                }
                                            }),
                                        ImageEntry::make('picture')
                                            ->label('Member Picture')
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
                                            }),
                                        TextEntry::make('district_id')
                                            ->label('District')
                                            ->state(function(Model $record){
                                                return District::whereId($record->district_id)->pluck('name');
                                            }),
                                        TextEntry::make('ward_id')
                                            ->label('Ward')
                                            ->state(function(Model $record){
                                                return Ward::whereId($record->ward_id)->pluck('name');
                                            }),
                                        TextEntry::make('street')
                                            ->placeholder('No Street Information provided'),
                                        TextEntry::make('block_no')
                                            ->placeholder('No Block No provided'),                                       
                                        TextEntry::make('house_no')
                                            ->placeholder('No House No provided')
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

                        Tabs\Tab::make('Spiritual Information')
                            ->schema([
                                ViewSection::make([
                                    TextEntry::make('jumuiya_id')
                                        ->label('Member Jumuiya')
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
                                ->description('Jumuiya Information'),

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
                                        ->placeholder('No Volunteering Requested'),
                                    TextEntry::make('sacrament_participation')
                                        ->placeholder('No Information provided'),
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
}
