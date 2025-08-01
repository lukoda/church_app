<?php

namespace App\Filament\Resources\LogOfferingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use App\Models\ChurchMember;
use Filament\Tables\Actions\Action;
use LaraZeus\Accordion\Forms\Accordion;
use LaraZeus\Accordion\Forms\Accordions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Grid;
use Filament\Forms\Get;
use App\Models\AuctionItemPayment;
use App\Models\AuctionItem;
use Filament\Notifications\Notification;

class ChurchAuctionAuctionItemPaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'church_auction';

    protected static ?string $title = 'Unverified Item Pledges';

    protected static ?string $badgeColor = 'danger';
    
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return AuctionItemPayment::whereIn('auction_item_id',AuctionItem::where('church_auction_id', $ownerRecord->church_auction->id)->pluck('id'))->where('verification_status', 'Unverified')->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->modifyQueryUsing(fn(Builder $query) => $query->join('auction_items', 'church_auctions.id', '=', 'auction_items.church_auction_id')->join('auction_item_payments', 'auction_items.id', '=', 'auction_item_payments.auction_item_id')->where('verification_status', 'Unverified')->orderBy('auction_item_payments.created_at', 'desc'))
            ->groups([
                Group::make('item_name')
                    ->label('Item')
                    ->getDescriptionFromRecordUsing(fn (Model $record): string => "Date Of Auction ". $record->auction_date),
            ])
            ->defaultGroup('item_name')
            ->columns([
                TextColumn::make('date_registered')
                ->date(),
                TextColumn::make('registered_by')
                ->formatStateUsing(function ($state, Model $record){
                    session('record_id', $record->id);
                    return ChurchMember::where('user_id', $state)->pluck('full_name')[0];
                }),
                TextColumn::make('amount_pledged')
                ->description(fn (Model $record) => $record->amount_remains),
                TextColumn::make('auction_items.auction_item_payments.amount_payed')
                ->label('Amount Payed'),
                TextColumn::make('payment_mode'),
                TextColumn::make('verification_status')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'Unverified' => 'warning',
                    'Verified' => 'success',
                })

            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Action::make('verify_payment')
                    ->fillForm(fn (Model $record): array => [
                        'receipt_picture' => $record->receipt_picture,
                        'due_amount' => $record->amount_remains,
                        'receipt_amount' => AuctionItemPayment::where('auction_item_id', AuctionItem::where('church_auction_id', $record->id)->pluck('id')[0])->pluck('amount_payed')[0]
                    ])
                    ->form([
                        Grid::make(3)
                            ->schema([
                                TextInput::make('due_amount')
                                    ->disabled(),
                                TextInput::make('receipt_amount')
                                    ->numeric()
                                    ->required(),
                                Select::make('verification_status')
                                    ->options([
                                        'Verified' => 'verified',
                                        'Unverified' => 'unverified'
                                    ])
                                    ->required(),
                            ]),
                        FileUpload::make('receipt_picture')
                            ->image()
                            ->disk('auctionPledgeReceipts')
                            ->downloadable(),                       
                    ])
                    ->action(function(array $data){
                        $verify_auction_payment = AuctionItemPayment::where('auction_item_id', AuctionItem::where('church_auction_id', $record->id)->pluck('id')[0])->first();
                        $verify_auction_payment->update([
                            'amount_payed' => $data['receipt_amount'],
                            'verification_status' => $data['verification_status']
                        ]);

                        Notification::make()
                            ->title('Amount verification successful')
                            ->success()
                            ->send();
                    })

            ])
            ->bulkActions([

            ]);
    }
}
