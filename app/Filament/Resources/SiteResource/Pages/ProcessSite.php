<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
// use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
// use Filament\Forms\Components\Actions;
// use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Section;
use Filament\Notifications\Notification;


class ProcessSite extends Page
{
    // use InteractsWithActions;
    // use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = SiteResource::class;

    protected static string $view = 'filament.resources.site-resource.pages.process-site';

    protected static ?string $title = 'Manage Progress of your sites.';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Site Information')
                    ->description('Your wordpress website is listed here.')
                    ->icon('heroicon-o-globe-alt')
                    ->aside()
                    ->schema([
                        TextEntry::make('title')->label('WordPress Site'),
                        TextEntry::make('url')->label('WordPress Site URL'),
                        IconEntry::make('status')
                            ->icon(fn (string $state): string => match ($state) {
                                '0' => 'heroicon-o-clock',
                                '1' => 'heroicon-o-check-circle',
                                '2' => 'heroicon-o-x-mark',
                                default => 'heroicon-o-clock'
                            }),
                        Actions::make([
                            Action::make('Start / Stop')
                                ->label(fn ($record) => $record->status == 0 ? "Start" : "Stop")
                                ->icon(fn ($record) => $record->status == 0 ? "heroicon-m-play" : "heroicon-m-stop")
                                ->requiresConfirmation()
                                ->action(function ($record) {
                                    // $record = $this->record;
                                    // Toggle the status code of the record
                                    $record->status = !$record->status;
                                    // Save the record to the database
                                    $record->save();
                                    // Show a success notification
                                    Notification::make()
                                        ->title('Status changed successfully.')
                                        ->seconds(5)
                                        ->success()
                                        ->send();
                                }),
                            Action::make('reset')
                                ->icon('heroicon-m-x-mark')
                                ->color('danger')
                                ->requiresConfirmation()
                                ->action(function ($record) {
                                    // $resetStars();
                                }),
                        ]),
                    ])->columns(1),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('Back')
                ->icon('heroicon-o-arrow-uturn-left')
                ->url(fn ($record): string => route('filament.admin.resources.sites.view', ['record' => $record->id])),
            \Filament\Actions\Action::make('Site List')
                ->color('info')
                ->icon('heroicon-o-list-bullet')
                ->url(fn ($record): string => route('filament.admin.resources.sites.index')),
        ];
    }
}
