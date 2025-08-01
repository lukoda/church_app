<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use App\Models\JumuiyaRevenue;
use App\Models\Jumuiya;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Filament\Notifications\Notification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ChurchJumuiyaRevenue extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.church-jumuiya-revenue';

    public static function canAccess(): bool
    {
        if(Auth::guard('web')->user()->hasRole(['Church Secretary', 'Senior Pastor', 'Pastor', 'SubParish Pastor']) && Auth::guard('web')->user()->checkPermissionTo('view-any JumuiyaRevenue')){
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
            ->body('Please contact your administrator.')
            ->danger()
            ->send();
            redirect()->to('/admin');
        }
    }

    public function table(Table $table) : Table
    {
        return $table
                ->query(function(){
                    if(Jumuiya::where('church_id', auth()->user()->church_id)->exists()){
                        return JumuiyaRevenue::query()->whereIn('jumuiya_id', Jumuiya::where('church_id', auth()->user()->church_id)->pluck('id'))->orderBy('created_at', 'desc');
                    }else{
                        return JumuiyaRevenue::query()->whereId(0);
                    }
                })
                ->columns([
                    TextColumn::make('date_recorded')
                    ->date(),

                    TextColumn::make('jumuiya.name')
                    ->label('Jumuiya'),

                    TextColumn::make('amount')
                    ->label('Offering')
                    ->formatStateUsing(fn($state) => number_format($state)),

                    TextColumn::make('jumuiya_attendance')
                    ->label('Attendance'),
                    
                    TextColumn::make('approval_status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Unverified' => 'warning',
                        'Verified' => 'success',
                    })                     
                ])
                ->emptyStateIcon('fas-money-bill-transfer')
                ->emptyStateHeading('No registered jumuiya offerings')
                ->emptyStateDescription('Once jumuiya revenues are registered will appear here')
                ->actions([
                    Action::make('verify_offerrings')
                    ->label(fn(Model $record) => $record->approval_status == 'Verified' ? 'Verify' : 'Unverify')
                    ->action(function(Model $record){
                        if($record->approval_status == 'Verified'){
                            $record->update([
                                'approval_status' => 'Unverified'
                            ]);

                            Notification::make()
                            ->title('Jumuiya Offering successfully Unverified')
                            ->success()
                            ->send();
                        }else if($record->approval_status == 'Unverified'){
                            $record->update([
                                'approval_status' => 'Verified'
                            ]);

                            Notification::make()
                            ->title('Jumuiya Offering successfully Verified')
                            ->success()
                            ->send();
                        }
                    })
                    ->visible(fn(Model $record) => Carbon::parse($record->date_recorded)->diffInDays(now()) > 0 ? false : true)
                ]);
    }
}
