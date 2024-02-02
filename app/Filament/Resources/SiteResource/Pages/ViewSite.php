<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewSite extends ViewRecord
{
    protected static string $resource = SiteResource::class;

    protected static ?string $title = 'Information about your website.';

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->icon('heroicon-m-pencil-square'),
            Action::make('Process')
                ->icon('heroicon-m-arrow-path-rounded-square')
                ->color('info')
                ->url(fn ($record): string => SiteResource::getUrl('process', ['record' => $record->id])),
            Action::make('activities')->url(fn ($record) => SiteResource::getUrl('activities', ['record' => $record]))->color('info')
        ];
    }
}
