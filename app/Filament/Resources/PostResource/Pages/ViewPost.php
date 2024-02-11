<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use App\Jobs\Hashnode\PublishAPost;
use App\Models\Setting;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class ViewPost extends ViewRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
            Action::make('activities')
                ->url(fn ($record) => PostResource::getUrl('activities', ['record' => $record]))
                ->color('info'),
            Action::make('tiptapEditor')
                ->url(fn ($record) => PostResource::getUrl('edit-tiptap', ['record' => $record]))
                ->color('info'),
            Action::make('Publish')
                ->label(fn ($record) => $record->published ? 'Re-Publish' : 'Publish')
                ->form([
                    Select::make('hashnodeId')
                        ->label('Hashnode Account')
                        ->options(Setting::query()->pluck('name', 'id'))
                        ->required(),
                    Checkbox::make('is_canonical')
                        ->label('Are you republishing?')
                        ->helperText(fn ($record) => $record->meta['link'])
                        ->hint('Use old blog link as Canonical URL')
                        ->visible(fn ($record) => !empty($record->meta['link']))
                ])
                ->action(function (array $data, $record): void {
                    dispatch(new PublishAPost($record->id, $data['hashnodeId'], $settings = [
                        'originalArticleURL' => $data['is_canonical'] ? true : false,
                    ]));
                    Notification::make()
                        ->title('Job dispatched successfully. Please refresh the page after some time to get the latest information.')
                        ->success()
                        ->send();
                })
                ->slideOver()
            // ->visible(fn ($record) => !$record->published)
            ,
        ];
    }
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Section::make('Post Publication')
                    ->description('Information about your publication of posts.')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('status')
                        // ->badge(fn ($record) => $record->status->getColor())
                            // ->icon(fn ($record) => $record->status->getIcon()),
                            ->badge(),
                        TextEntry::make('title')
                            ->label('Website Title')
                            ->weight(FontWeight::Bold)
                            ->columnSpan(3),
                        TextEntry::make('meta.post.url')
                            ->label('URL')
                            ->default('N/A')
                            // ->formatStateUsing(fn ($record) => !empty($record->meta['post']['url']) ? $record->meta['post']['url']: "N/A")
                            ->url(fn ($record) => !empty($record->meta['post']['url']) ? $record->meta['post']['url'] : "/")
                            ->openUrlInNewTab()
                            ->weight(FontWeight::Bold)
                            ->columnSpan(4)
                            ->badge()
                            ->visible(fn ($record) => $record->status->value === 3),

                    ]),
                Tabs::make('Tabs')
                    ->tabs([
                        Tabs\Tab::make('markdown')
                            ->label("Markdown Output")
                            ->icon('heroicon-m-pencil-square')
                            ->iconPosition(IconPosition::After)
                            ->schema([
                                TextEntry::make('output')->markdown(),
                            ]),
                        Tabs\Tab::make('preview')
                            ->label("Original Preview")
                            ->icon('heroicon-m-eye')
                            ->iconPosition(IconPosition::After)
                            ->schema([
                                TextEntry::make('body')->html(),
                            ]),
                    ])
                    ->columnSpan(2)

            ]);
    }
}
