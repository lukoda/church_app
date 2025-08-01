<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ChurchServiceRequestResource\Pages;
use App\Filament\Resources\ChurchServiceRequestResource\RelationManagers;
use App\Models\ChurchMember;
use App\Models\ChurchService;
use App\Models\ChurchServiceRequest;
use App\Models\Jumuiya;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class ChurchServiceRequestResource extends Resource
{
    protected static ?string $model = ChurchServiceRequest::class;

    protected static ?string $navigationIcon = 'fas-hands-praying';

    protected static ?string $navigationGroup = 'Church Member Requests';

    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->checkPermissionTo('view-any ChurchServiceRequest');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('church_service_id')
                    ->reactive()
                    ->label('Church Service')
                    ->options(ChurchService::all()->where('church_id', auth()->user()->church_id)->pluck('title', 'id'))
                    ->required()
                    ->afterStateUpdated(function(Set $set, $state){
                        $set('church_service_description', ChurchService::whereId($state)->pluck('description')[0]);
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
                    ->default(auth()->user()->churchMember->id),

                Hidden::make('status')
                    ->default('active'),
                    
                Hidden::make('approval_status')
                    ->default('Pending Approval'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                    ->formatStateUsing(function(Model $record) {
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
                    ->html()
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->disabled(!(auth()->user()->checkPermissionTo('update ChurchServiceRequest')))
                ->visible(auth()->user()->checkPermissionTo('update ChurchServiceRequest')),
                Tables\Actions\DeleteAction::make()
                ->disabled(!(auth()->user()->checkPermissionTo('delete ChurchServiceRequest')))
                ->visible(auth()->user()->checkPermissionTo('delete ChurchServiceRequest')),
                Action::make('approve')
                    ->fillForm(fn(ChurchServiceRequest $record): array => [
                        'status' => $record->ChurchService->status,
                    ])
                    ->form([

                        Textarea::make('jumuiya_chairperson_comment')
                            ->label('comment')
                            ->required()
                    ])
                    ->action(function(array $data, ChurchServiceRequest $record, array $arguments){
                        if($arguments['status'] == true){
                            $record->jumuiya_chairperson_comment = $data['jumuiya_chairperson_comment'];
                            $record->jumuiya_chairperson_approval_status = 'Approved';
                            $record->save();
                            Notification::make()
                                ->title('Successfully Approved Member Request')
                                ->success()
                                ->send();
                        }else if($arguments['status'] = false){
                            $record->jumuiya_chairperson_comment = $data['jumuiya_chairperson_comment'];
                            $record->jumuiya_chairperson_approval_status = 'Not Approved';
                            $record->save();
                            Notification::make()
                                ->title('Successfully Unapproved Member Request')
                                ->success()
                                ->send();
                        }

                    })
                    ->disabled(! (auth()->user()->hasRole('Jumuiya Chairperson')))
                    ->visible((auth()->user()->hasRole('Jumuiya Chairperson')))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalFooterActions(fn (StaticAction $action): array => [
                        $action->makeModalSubmitAction('Approve', arguments: ['status' => true])->color('success'),
                        $action->makeModalSubmitAction('Disapprove', arguments: ['status' => false])->color('danger')
                    ]),

                //viewable if user has role of secretary or above
                Action::make('approve_request')
                    ->fillForm(fn(ChurchServiceRequest $record): array => [
                        'jumuiya_chairperson_comment' => $record->jumuiya_chairperson_comment,
                        'status' => $record->ChurchService->status,
                        'jumuiya_chairperson_approval_status' => $record->jumuiya_chairperson_approval_status
                    ])
                    ->form([
                        // Select::make('status')
                        // ->options([
                        //     'active' => 'Active',
                        //     'inactive' => 'Inactive'
                        // ])
                        // ->disabled(),
                        TextInput::make('jumuiya_chairperson_approval_status')
                        ->disabled(),

                        Textarea::make('jumuiya_chairperson_comment')
                            ->disabled()
                            ->columnSpanFull(),

                        // Section::make('approval_details')
                        //     ->schema([
                                // Select::make('approval_status')
                                //     ->options([
                                //         'Approved' => 'Approved',
                                //         'Not Approved' => 'Not Approved',
                                //     ])
                                //     ->required(),

                                TextInput::make('approval_comment')
                                    ->label('comment'),

                            // ])
                    ])
                    ->action(function(array $data, ChurchServiceRequest $record, array $arguments){
                        if($arguments['status'] == true){
                            $record->approval_status = 'Approved';
                            $record->approval_comment = $data['approval_comment'] ?? Null;
                            $record->approved_by = auth()->user()->id;
                            $record->save();

                            Notification::make()
                                ->title('Successfully Unapproved Member Request')
                                ->success()
                                ->send();
                        }else if($arguments['status'] == false){
                            $record->approval_status = 'Not Approved';
                            $record->approval_comment = $data['approval_comment'] ?? Null;
                            $record->approved_by = auth()->user()->id;
                            $record->save();

                            Notification::make()
                                ->title('Successfully Approved Member Request')
                                ->success()
                                ->send();
                        }

                    })
                    ->disabled(! (auth()->user()->hasRole('Church Secretary')))
                    ->visible(fn(ChurchServiceRequest $record) => (auth()->user()->hasRole('Church Secretary') && $record->jumuiya_chairperson_approval_status != Null))
                    ->modalSubmitAction(false)
                    ->modalCancelAction(false)
                    ->modalFooterActions(fn (StaticAction $action): array => [
                        $action->makeModalSubmitAction('Approve', arguments: ['status' => true])->color('success'),
                        $action->makeModalSubmitAction('Disapprove', arguments: ['status' => false])->color('danger')
                    ])

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make()
                    // ->disabled(!(auth()->user()->checkPermissionTo('delete ChurchServiceRequest')))
                    // ->visible(auth()->user()->checkPermissionTo('delete ChurchServiceRequest')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageChurchServiceRequests::route('/'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'active')->whereHas('ChurchService', function(Builder $query){
            $query->where('church_id', auth()->user()->church_id);
        });
    }
}
