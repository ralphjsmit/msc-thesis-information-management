<?php

namespace Filament\Auth\MultiFactor\GoogleTwoFactor\Actions;

use Closure;
use Filament\Actions\Action;
use Filament\Auth\MultiFactor\GoogleTwoFactor\Contracts\HasGoogleTwoFactorAuthentication;
use Filament\Auth\MultiFactor\GoogleTwoFactor\Contracts\HasGoogleTwoFactorAuthenticationRecovery;
use Filament\Auth\MultiFactor\GoogleTwoFactor\GoogleTwoFactorAuthentication;
use Filament\Facades\Filament;
use Filament\Forms\Components\OneTimeCodeInput;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class DisableGoogleTwoFactorAuthenticationAction
{
    public static function make(GoogleTwoFactorAuthentication $googleTwoFactorAuthentication): Action
    {
        $isRecoverable = $googleTwoFactorAuthentication->isRecoverable();

        return Action::make('disableGoogleTwoFactorAuthentication')
            ->label(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.label'))
            ->color('danger')
            ->icon(Heroicon::LockOpen)
            ->link()
            ->modalWidth(Width::Medium)
            ->modalIcon(Heroicon::OutlinedLockOpen)
            ->modalHeading(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.heading'))
            ->modalDescription(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.description'))
            ->schema([
                OneTimeCodeInput::make('code')
                    ->label(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.code.label'))
                    ->belowContent(fn (Get $get): Action => Action::make('useRecoveryCode')
                        ->label(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.code.actions.use_recovery_code.label'))
                        ->link()
                        ->action(fn (Set $set) => $set('useRecoveryCode', true))
                        ->visible(fn (): bool => $isRecoverable && (! $get('useRecoveryCode'))))
                    ->validationAttribute(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.code.validation_attribute'))
                    ->required(fn (Get $get): bool => (! $isRecoverable) || blank($get('recoveryCode')))
                    ->rule(function () use ($googleTwoFactorAuthentication): Closure {
                        return function (string $attribute, mixed $value, Closure $fail) use ($googleTwoFactorAuthentication): void {
                            if (is_string($value) && $googleTwoFactorAuthentication->verifyCode($value)) {
                                return;
                            }

                            $fail(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.code.messages.invalid'));
                        };
                    }),
                TextInput::make('recoveryCode')
                    ->label(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.recovery_code.label'))
                    ->validationAttribute(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.recovery_code.validation_attribute'))
                    ->password()
                    ->revealable(Filament::arePasswordsRevealable())
                    ->rule(function () use ($googleTwoFactorAuthentication): Closure {
                        return function (string $attribute, mixed $value, Closure $fail) use ($googleTwoFactorAuthentication): void {
                            if (blank($value)) {
                                return;
                            }

                            if (is_string($value) && $googleTwoFactorAuthentication->verifyRecoveryCode($value)) {
                                return;
                            }

                            $fail(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.form.recovery_code.messages.invalid'));
                        };
                    })
                    ->visible(fn (Get $get): bool => $isRecoverable && $get('useRecoveryCode'))
                    ->live(onBlur: true),
            ])
            ->modalSubmitAction(fn (Action $action) => $action
                ->label(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.modal.actions.submit.label')))
            ->action(function () use ($googleTwoFactorAuthentication, $isRecoverable): void {
                /** @var HasGoogleTwoFactorAuthentication&HasGoogleTwoFactorAuthenticationRecovery $user */
                $user = Filament::auth()->user();

                DB::transaction(function () use ($googleTwoFactorAuthentication, $isRecoverable, $user): void {
                    $googleTwoFactorAuthentication->saveSecret($user, null);

                    if ($isRecoverable) {
                        $googleTwoFactorAuthentication->saveRecoveryCodes($user, null);
                    }
                });

                Notification::make()
                    ->title(__('filament-panels::auth/multi-factor/google-two-factor/actions/disable.notifications.disabled.title'))
                    ->success()
                    ->icon(Heroicon::OutlinedLockOpen)
                    ->send();
            })
            ->rateLimit(5);
    }
}
