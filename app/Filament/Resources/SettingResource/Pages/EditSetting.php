<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditSetting extends EditRecord
{
    protected static string $resource = SettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Action::make('Validate')
                // ->form([
                //     Select::make('hashnodeId')
                //         ->label('Hashnode Account')
                //         ->options(Setting::query()->pluck('name', 'id'))
                //         ->required(),
                // ])
                ->action(function ($record): void {
                    $hashnodeInstence = new \App\Integrations\Hashnode($record->id);
                    $res = $hashnodeInstence->checkCurrentUser();

                    if ($res) {
                        Notification::make()
                            ->title('Authented successfully.')
                            ->success()
                            ->send();
                    } else {
                        Notification::make()
                            ->title('Error happend. Could not authenticate.')
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
