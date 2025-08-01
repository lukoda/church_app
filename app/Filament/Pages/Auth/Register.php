<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Page;
use Filament\Pages\Auth\Register as BaseRegister;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\TextInput;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
// use Filament\Http\Responses\Auth\Contracts\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Facades\Filament;
use App\Http\Response\RegistrationResponse;
use App\Models\Dinomination;
use App\Models\Church;
use App\Models\ChurchDistrict;
use App\Models\Diocese;
use App\Models\Region;
use App\Models\District;
use App\Models\Ward;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use App\Models\User;

class Register extends BaseRegister
{
    protected function getPasswordFormComponent(): Component
    {
        return TextInput::make('password')
            ->label(__('Password'))
            ->placeholder('Enter password')
            ->password()
            ->required()
            ->rule(Password::default())
            ->dehydrateStateUsing(fn ($state) => Hash::make($state))
            ->same('passwordConfirmation')
            ->validationAttribute(__('filament-panels::pages/auth/register.form.password.validation_attribute'));
    }

    protected function getPasswordConfirmationFormComponent(): Component
    {
        return TextInput::make('passwordConfirmation')
            ->label(__('Confirm Password'))
            ->placeholder('Repeat password')
            ->password()
            ->required()
            ->dehydrated(false);
    }

    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                            Select::make('dinomination_id')
                                ->label('Dinomination')
                                ->options(Dinomination::all()->pluck('name', 'id'))
                                ->required(),

                            // Select::make('search_key')
                            //     ->options([
                            //         'pastor' => 'pastor',
                            //         'region' => 'region',
                            //         'district' => 'district',
                            //         'ward' => 'ward',
                            //         'name' => 'church name'
                            //     ])
                            //     ->reactive()
                            //     ->required(),

                            Select::make('church_id')
                                ->searchable()
                                ->label('church')
                                // ->options(function (Get $get){
                                //     return Church::all()->pluck('name', 'id')->toArray();
                                // })
                                ->getSearchResultsUsing(function(string $search, Get $get): array{
                                    return Church::orWhereIn('region_id', Region::where('name', 'like', '%'.$search.'%')->pluck('id'))
                                                    ->orWhereIn('district_id', District::where('name', 'like', '%'.$search.'%')->pluck('id'))
                                                    ->orWhereIn('ward_id', Ward::where('name', 'like', '%'.$search.'%')->pluck('id'))
                                                    ->orWhere('name', 'like', '%'.$search.'%')
                                                    ->orWhereIn('church_district_id', ChurchDistrict::where('name', 'like', '%'.$search.'%')->pluck('id'))
                                                    ->pluck('name','id')->toArray();
                                })
                                ->getOptionLabelsUsing(function(array $values): array {
                                    return Church::whereIn('id', $values)->pluck('name','id')->toArray();
                                })->required(),

                            TextInput::make('phone')
                                ->label(__('Phone Number'))
                                ->tel()
                                ->helperText('0789******')
                                ->maxLength(10)
                                ->required()
                                ->extraInputAttributes(['tabindex' => 1]),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ])
                    ->statePath('data'),
            ),
        ];
    }

    public function register(): ?RegistrationResponse
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/register.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/register.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/register.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return null;
        }

        $data = $this->form->getState();

        if(User::where('phone', $data['phone'])->exists()){
            return app(RegistrationResponse::class, ['phone' => $data['phone']]);
        }else{
            $user = $this->getUserModel()::create($data);

            $this->sendEmailVerificationNotification($user);
    
            Filament::auth()->login($user);
    
            session()->regenerate();
    
            return app(RegistrationResponse::class);
        }
    }
}
