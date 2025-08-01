<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\ChurchServiceRequest;
use App\Models\ChurchService;
use App\Models\ChurchMember;
use App\Models\Jumuiya;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Actions\CreateAction;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Grid;
use Illuminate\Support\Facades\Auth;

class RequestChurchService extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'fas-hands-praying';

    protected static string $view = 'filament.pages.request-church-service';

    protected static ?string $navigationGroup = 'Church Services';

    public ?array $church_services = null;

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole('Church Member') && Auth::guard('web')->user()->checkPermissionTo('view ChurchServiceRequest')){
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
            ->body('Please contact your Administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Auth::guard('web')->user()->checkPermissionTo('view ChurchServiceRequest');
    }

    public function setChurchServices()
    {
        $this->church_services = ChurchService::where('church_id', auth()->user()->church_id)->where('status', 'active')->get()->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Requested Church Services')
            ->headerActions([
                CreateAction::make()
                    ->label('Request Church Service')
                    ->model(ChurchServiceRequest::class)
                    ->link()
                    ->form([
                        Grid::make(2)
                            ->schema([
                                Select::make('church_service_id')
                                    ->reactive()
                                    ->label('Church Service')
                                    ->options(ChurchService::all()->where('church_id', auth()->user()->church_id)->where('status', 'active')->pluck('title', 'id'))
                                    ->required()
                                    ->afterStateUpdated(function(Set $set, $state){
                                        $set('church_service_description', ChurchService::whereId($state)->pluck('description')[0]);
                                    }),
    
                                Select::make('requesting_service_for')
                                    ->reactive()
                                    ->options([
                                        'Member' => 'On Behalf Of Member',
                                        'Personal' => 'Personal'
                                    ])
                                    ->required()
                                    ->visible(auth()->user()->hasRole(['Committee Member', 'Jumuiya Chairperson'])),
                            ]),
                        
                        Checkbox::make('is_churchMember')
                            ->reactive()
                            ->default(true)
                            ->visible(function(Get $get){
                                if(auth()->user()->hasRole('Committee') || auth()->user()->hasRole('Jumuiya Chairperson')){
                                    if($get('requesting_service_for') == 'Member'){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }else {
                                    return true;
                                }
                            })
                            ->required(),

                        Select::make('church_member')
                            ->label('Member Name')
                            ->reactive()
                            ->searchable()
                            ->options(function(){
                                if(auth()->user()->churchMember){
                                    return ChurchMember::where('church_id', auth()->user()->churchMember->church_id)->where('status', 'active')->pluck('full_name', 'id');
                                }else{
                                    return [];
                                }
                            })
                            ->required()
                            ->visible(function(Get $get){
                                if($get('requesting_service_for') == 'Member' && $get('is_churchMember') == true){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        TextInput::make('full_name')
                            ->visible(function(Get $get){
                                if($get('is_churchMember') == false){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        Hidden::make('requested_on_behalf_by')
                            ->reactive()
                            ->default(auth()->user()->churchMember ? auth()->user()->churchMember->id : Null)
                            ->visible(function(Get $get){
                                if($get('requesting_service_for') == 'Member'){
                                    return true;
                                }else{
                                    return false;
                                }
                            }),

                        DatePicker::make('date_requested')
                            ->label('Service Date')
                            ->native(false)
                            ->suffixIcon('heroicon-o-calendar')
                            ->default(now()),

                        RichEditor::make('church_service_description')
                            ->disableToolbarButtons([
                                'codeBlock',
                                'strike',
                                'link',
                                'attachFiles'
                            ])
                            ->visible(function(Get $get){
                                if($get('church_service_id')){
                                    return true;
                                }else{
                                    return false;
                                }
                            })
                            ->columnSpanFull()
                            ->disabled(),

                        RichEditor::make('message')
                            ->disableToolbarButtons([
                                'codeBlock',
                                'strike',
                                'link',
                                'attachFiles'
                            ])
                            ->columnSpanFull()
                            ->helperText('Please, provide description about service requested'),

                        Hidden::make('church_member_id')
                            ->default(auth()->user()->churchMember ? auth()->user()->churchMember->id : Null)
                            ->visible(function(Get $get){
                                if(blank($get('requesting_service_for'))){
                                    return false;
                                }else{
                                    return true;
                                }
                            }),

                        Hidden::make('status')
                            ->default('active'),
                            
                        Hidden::make('approval_status')
                            ->default('Pending Approval'),
                ])
                ->using(function(array $data, string $model): Model {
                    return $model::create([
                        'requesting_service_for' => array_key_exists('requesting_service_for', $data) ? $data['requesting_service_for'] : Null,
                        'is_church_member' => array_key_exists('is_church_member', $data) ? $data['is_church_member'] : Null,
                        'full_name' => array_key_exists('full_name', $data) ? $data['full_name'] : Null,
                        'request_on_behalf_by' => array_key_exists('request_on_behalf_by', $data) ? $data['request_on_behalf_by'] : Null,
                        'church_member_id' => array_key_exists('church_member', $data) ? $data['church_member'] : $data['church_member_id'],
                        'church_service_id' => $data['church_service_id'],
                        'date_requested' => $data['date_requested'],
                        'message' => $data['message'],
                        'approval_status' => $data['approval_status'],
                        'status' => $data['status']
                    ]);
                })
                ->successNotification(
                    Notification::make()
                        ->success()
                        ->title('Service Request Sent')
                        ->body('Your request has been sent successfully.'),
                )
                ->visible(auth()->user()->checkPermissionTo('create ChurchServiceRequest') && auth()->user()->hasRole('Church Member') && auth()->user()->churchMember)
            ])
            ->query(function(){
                if(auth()->user()->churchMember){
                    return ChurchServiceRequest::query()->where('church_member_id', auth()->user()->churchMember->id)->orderBy('created_at', 'desc');
                }else{
                    return ChurchServiceRequest::query()->whereId(0);
                }
            })
            ->columns([
                TextColumn::make('date_requested')
                    ->label('Service Date')
                    ->date(),
                TextColumn::make('church_service_id')
                    ->label('Church Service')
                    ->formatStateUsing(function(Model $record){
                        return ChurchService::whereId($record->church_service_id)->pluck('title')[0];
                    }),
                TextColumn::make('church_member_id')
                    ->label('Church Member')
                    ->formatStateUsing(function(Model $record){
                        return ChurchMember::whereId($record->church_member_id)->pluck('full_name')[0];
                    })
                    ->description(function(Model $record) {
                        $jumuiya = Jumuiya::whereIn('id', ChurchMember::whereId($record->church_member_id)->pluck('jumuiya_id'))->first();
                        if($jumuiya != Null){
                            return "From Jumuiya ".$jumuiya->name ?? 'No jumuiya';
                        }else{
                            return 'No Jumuiya';
                        }
                    }),
                TextColumn::make('message')
                    ->wrap()
                    ->limit(50)
                    ->html(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'active' => 'success',
                        'inactive' => 'warning'
                    }),

                TextColumn::make('approval_status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state){
                        'Approved' => 'success',
                        'Pending Approval' => 'warning',
                        'Not Approved' => 'danger'
                    }),
            ])
            ->actions([
                    Action::make('deactivate request')
                        ->label(fn(Model $record) => $record->status == 'active' ? 'Deactivate Request' : 'Activate Request')
                        ->form([
                            CheckBox::make('reschedule_date')
                                ->default(function(Model $record){
                                    if($record->date_requested > now()){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->disabled(function(Model $record){
                                    if($record->date_requested > now()){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                })
                                ->hidden(fn(Model $record) => $record->status == 'active' ? true : false),

                            DatePicker::make('date_requested')
                                ->label('Service Date')
                                ->minDate(now()->subDay())
                                ->default(now())
                                ->visible(fn(Get $get) => $get('reschedule_date')),
                        ])
                        ->action(function(Model $record){
                            if($record->status == 'active'){
                                $record->update([
                                    'status' => 'inactive'
                                ]);
                            }else{
                                if($record->status == 'inactive'){
                                    $record->update([
                                        'status' => 'active',
                                        'date_requested' => array_key_exists('date_requested', $data) ? $data['date_requested'] : $record->date_requested
                                    ]);
                                }
                            }
                        })
                        ->visible(function(Model $record){
                            if($record->jumuiya_chairperson_comment != Null || $record->approval_status != 'Pending Approval'){
                                return false;
                            }else{
                                return true;
                            }
                        }),

                    DeleteAction::make()
                        ->visible(function(Model $record){
                            if($record->jumuiya_chairperson_comment != Null || $record->approval_status != 'Pending Approval'){
                                return false;
                            }else{
                                return true;
                            }
                        }),
            ]);
    }
}
