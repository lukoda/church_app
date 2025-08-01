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
use Illuminate\Database\Eloquent\Model;
use App\Models\AuctionItem;

class ChurchAuctionAuctionItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'church_auction';

    protected static ?string $title = 'All Pledges';

    protected static ?string $badgeColor = 'warning';
    
    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return AuctionItem::where('church_auction_id', $ownerRecord->church_auction->id)->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item_name')
            ->modifyQueryUsing(fn(Builder $query) => $query->join('auction_items', 'church_auctions.id', '=', 'auction_items.church_auction_id')->orderBy('auction_items.created_at', 'desc'))
            ->columns([
                TextColumn::make('item_name'),
                TextColumn::make('name')
                 ->formatStateUsing(fn($state) => str_replace('"]','',str_replace('["', '',$state))),
                TextColumn::make('card_no')
                    ->default('No Card No'),
                TextColumn::make('phone_no'),
                TextColumn::make('amount_pledged')
                    ->label('Pledge')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    )
                    ->description(fn (Model $record): string => $record->amount_payed),
                TextColumn::make('amount_remains')
                    ->label('Due Amount')
                    ->numeric(
                        decimalPlaces: 0,
                        decimalSeparator: '.',
                        thousandsSeparator: ',',
                    ),
                TextColumn::make('registered_on')
                    ->date(),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Pending Auction' => 'warning',
                        'Auction Complete' => 'success',
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
