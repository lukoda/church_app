<?php

namespace App\Filament\Resources\LogOfferingResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Illuminate\Database\Eloquent\Model;
use App\Models\ChurchMember;
use App\Models\AuctionItem;
use App\Models\AuctionItemPayment;

class ChurchAuctionAuctionItemPledgesRelationManager extends RelationManager
{
    protected static string $relationship = 'church_auction';

    protected static ?string $title = 'Verified Item Pledges';

    protected static ?string $badgeColor = 'success';
    
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return AuctionItemPayment::whereIn('auction_item_id',AuctionItem::where('church_auction_id', $ownerRecord->church_auction->id)->pluck('id'))->where('verification_status', 'Verified')->count() ?? 0;
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('item_name')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->modifyQueryUsing(fn(Builder $query) => $query->join('auction_items', 'church_auctions.id', '=', 'auction_items.church_auction_id')->join('auction_item_payments', 'auction_items.id', '=', 'auction_item_payments.auction_item_id')->where('verification_status', 'Verified')->orderBy('auction_items.created_at', 'desc'))
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
                ->formatStateUsing(fn($state) => ChurchMember::where('user_id', $state)->pluck('full_name')[0]),
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

            ])
            ->bulkActions([

            ]);
    }
}
