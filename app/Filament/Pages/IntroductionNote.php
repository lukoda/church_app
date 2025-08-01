<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\IntroductionNote as Note;
use Filament\Tables\Columns\TextColumn;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use App\Models\Church;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Filament\Forms\set;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class IntroductionNote extends Page implements HasTable
{
    use InteractsWithTable; 

    protected static ?string $navigationIcon = 'fas-person-chalkboard';

    protected static string $view = 'filament.pages.introduction-note';

    protected static ?string $navigationGroup = 'Church Services';

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Member') && Auth::guard('web')->user()->checkPermissionTo('view IntroductionNote')){
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

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->checkPermissionTo('view IntroductionNote') && auth()->user()->hasRole('Church Member');
    }

    public function table(Table $table): Table
    {
        return $table
                ->heading('Introduction Note Requests')
                ->headerActions([
                    CreateAction::make()
                        ->label('Create IntroductionNote')
                        ->model(Note::class)
                        ->link()
                        ->form([
                        Hidden::make('from_church_id')
                            ->default(auth()->user()->church_id),
        
                        Hidden::make('church_member_id')
                            ->default(auth()->user()->churchMember ? auth()->user()->churchMember->id : Null),
                            
                        Grid::make(3)
                            ->schema([            
                                Select::make('title')
                                    ->label('Purpose')
                                    ->options([
                                        'Travel' => 'Travel',
                                        'Holiday' => 'Holiday',
                                        'Work' => 'Work',
                                        'Family' => 'Family',
                                        'Other' => 'Other'
                                    ])
                                    ->required(),
                
                                DatePicker::make('date_requested')
                                    ->label('Date Of Leave')
                                    ->default(now())
                                    ->minDate(now())
                                    ->required(),
        
                                TextInput::make('sundays_on_leave')
                                    ->numeric()
                                    ->required()
                            ]),
                        
                        RichEditor::make('description')
                                ->label('Message')
                                ->disableToolbarButtons([
                                    'codeBlock',
                                    'strike',
                                    'link',
                                    'attachFiles'
                                ])
                                ->columnSpan('full'),
        
                        Section::make('Destination details')
                            ->columns(4)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('region_id')
                                    ->preload()
                                    ->reactive()
                                    ->searchable()
                                    ->label('Region')
                                    ->options(Region::pluck('name', 'id')->toArray())
                                    ->afterStateUpdated(function (Set $set): void {
                                        $set('district_id', null);
                                        $set('ward_id', null);
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
                                    ->reactive(),
        
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
                                    }),
        
                                Select::make('to_church_id')
                                    ->label('To Church')
                                    ->options(function(Get $get){
                                        if(blank($get('region_id')) && blank($get('district_id')) && blank($get('ward_id'))){
                                            return [];
                                        }else{
                                            if(blank($get('ward_id'))){
                                                return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->pluck('name', 'id');
                                            }else{
                                                return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->where('ward_id', $get('ward_id'))->pluck('name', 'id');
                                            }                               
                                        }
                                    })
                                    ->searchable()
                                ]),
        
                                Hidden::make('status')
                                    ->default('active'),

                                Hidden::make('approval_status')
                                    ->default('Pending Approval')
                        ])
                        ->using(function(array $data, string $model): Model {
                            return $model::create([
                                'from_church_id' => $data['from_church_id'],
                                'to_church_id' => $data['to_church_id'] ?? Null,
                                'church_member_id' => auth()->user()->churchMember->id,
                                'title' => $data['title'],
                                'description' => $data['description'],
                                'date_requested' => $data['date_requested'],
                                'sundays_on_leave' => $data['sundays_on_leave'],
                                'date_of_return' => Carbon::parse($data['date_requested'])->startOfWeek()->subDay()->addWeeks($data['sundays_on_leave']),
                                'region_id' => $data['region_id'] ?? Null,
                                'district_id' => $data['district_id'] ?? Null,
                                'ward_id' => $data['ward_id'],
                                'status' => $data['status'],
                                'approval_status' => array_key_exists('to_church_id', $data) ? 'Conditional Approval' : $data['approval_status']
                            ]);
                        })
                        ->successNotification(
                            Notification::make()
                                ->success()
                                ->title('IntroductionNote Request Sent')
                                ->body('Your request has been sent successfully.'),
                        )
                        ->visible(auth()->user()->hasRole('Church Member') && auth()->user()->checkPermissionTo('create IntroductionNote') && auth()->user()->churchMember)
                        ->disabled(! (auth()->user()->hasRole('Church Member') && auth()->user()->checkPermissionTo('create IntroductionNote')))

                ])
                ->query(function(){
                    if(auth()->user()->churchMember){
                        return Note::query()->where('church_member_id', auth()->user()->churchMember->id)->orderBy('created_at', 'desc');
                    }else{
                        return Note::query()->whereId(0);
                    }
                })
                ->columns([
                    TextColumn::make('title'),
                    TextColumn::make('date_requested')
                        ->date(),
                    TextColumn::make('address')
                        ->label('Destination Address')
                        ->default(function($record){
                            $region = Region::whereId($record->region_id)->pluck('name');
                            $district = District::whereId($record->district_id)->pluck('name');
                            $ward = Ward::whereId($record->ward_id)->pluck('name');
                            if($record->region_id == Null){
                                return 'No address specified';
                            }else{
                                if($record->district_id == Null){
                                    return "{$region[0]}";
                                }else if($record->ward_id == Null){
                                    return "{$district[0]}, {$region[0]}";
                                }else{
                                    return "{$ward[0]}, {$district[0]} {$region[0]}";
                                }
                            }
                        }),
                    TextColumn::make('from_church_id')
                        ->label('From Church')
                        ->formatStateUsing(function($state){
                            return Church::whereId($state)->pluck('name')[0];
                        }),
                    TextColumn::make('to_church_id')
                        ->label('To Church')
                        ->formatStateUsing(function($state){
                            if(blank($state)){
                                return '-';
                            }else{
                                return Church::whereId($state)->pluck('name')[0];
                            }
                        }),   
                    TextColumn::make('approval_status')
                        ->badge()
                        ->color(fn(string $state): string => match ($state){
                            'Pendng Approval' => 'warning',
                            'Conditional Approval' => 'warning',
                            'Approved' => 'success',
                            'Not Approved' => 'gray'
                        })
                ])
                ->emptyStateIcon('fas-person-chalkboard')
                ->emptyStateHeading('No introduction notes requested')
                ->emptyStateDescription('Once you request for an introduction note it will appear here.')
                ->actions([
                    Action::make('confirm_destination')
                    ->label(fn(Model $record) => $record->to_church_id != Null ? ($record->approval_status != Null ? $record->approval_status : 'Pending Approval') : 'Confirm Destination')
                    ->fillForm(fn(Model $record): array => [
                        'region_id' => $record->region_id,
                        'district_id' => $record->district_id,
                        'ward_id' => $record->ward_id
                    ])
                    ->form([
                            Grid::make(4)
                                ->schema([
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
                                        ->required(),

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
                                        ->required(),

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
                                        }),

                                    Select::make('to_church_id')
                                        ->label('To Church')
                                        ->options(function(Get $get){
                                            if(blank($get('region_id')) && blank($get('district_id')) && blank($get('ward_id'))){
                                                return [];
                                            }else{
                                                if(blank($get('ward_id'))){
                                                    return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->pluck('name', 'id');
                                                }else{
                                                    return Church::where('region_id', $get('region_id'))->where('district_id', $get('district_id'))->where('ward_id', $get('ward_id'))->pluck('name', 'id');
                                                }
                                            }
                                        })
                                        ->searchable()
                                        ->required()
                                ])
                            ])
                            ->action(function(array $data, Note $record){
                                $record->region_id = $data['region_id'];
                                $record->district_id = $data['district_id'];
                                $record->ward_id = $data['ward_id'];
                                $record->to_church_id = $data['to_church_id'];
                                $record->approval_status = $record->to_church_id !== Null ? ($record->approval_status == 'Approved' ? 'Approved' : 'Conditional Approval') :'Conditional Approval';
                                $record->save();

                                Notification::make()
                                    ->title('Approval request sent')
                                    ->success()
                                    ->send();
                            })
                            ->visible(function($record){
                                if($record->to_church_id !== Null){
                                    return false;
                                }else{
                                    return true;
                                }
                            })
                            ->disabled(function($record){
                                if($record->to_church_id == Null){
                                    return false;
                                }else{
                                    return true;
                                }
                            }),
                    EditAction::make()
                        ->visible(function(Note $record){
                            if($record->status == 'Approved'){
                                return false;
                            }else{
                                if(auth()->user()->hasRole('Church Member') && auth()->user()->checkPermissionTo('update IntroductionNote')){
                                    return true;
                                }else{
                                    return false;
                                }
                            }
                        }),
                ]);
    }

}
