<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;

    protected function beforeCreate(): void
    {
        // We will update
        // if (empty($this->record->status)) {
        //                 Notification::make()
        //         ->warning()
        //         ->title('Sorry! Something went wrong.')
        //         ->body('We can not connect with this. Please make sure all the information is correct.')
        //         ->persistent()
        //         // ->actions([
        //         //     Action::make('subscribe')
        //         //         ->button()
        //         //         // ->url(route('subscribe'), shouldOpenInNewTab: true),
        //         // ])
        //         ->send();
        //         $this->halt();
        // }
    }
}
