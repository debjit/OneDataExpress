<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SiteResource\Pages;
use App\Filament\Resources\SiteResource\RelationManagers\PostsRelationManager;
use App\Jobs\Hashnode\PublishAllSitePosts;
use App\Models\Setting;
use App\Models\Site;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Actions;
use Filament\Infolists\Components\Actions\Action;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
// use Illuminate\Database\Eloquent\Relations\Relation;
// use App\Filament\Resources\SiteResource\RelationManagers;
use Filament\Tables\Table;
use Filament\Notifications\Notification;

class SiteResource extends Resource
{
    protected static ?string $model = Site::class;

    protected static ?string $navigationIcon = 'heroicon-o-globe-alt';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title')
                    ->required(),
                TextInput::make('url')
                    ->required(),
                TextInput::make('key'),
                TextInput::make('value'),
                MarkdownEditor::make('description')
                    // ->rows(8)
                    ->columnSpan(2)
                // ->collapsable()
                // ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('url'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PostsRelationManager::class,
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([

            Section::make('Site Information')
                ->description('Your wordpress website is listed here.')
                ->icon('heroicon-o-globe-alt')
                ->columns(2)
                ->schema([
                    TextEntry::make('title')
                        ->label('Website Title')
                        ->weight(FontWeight::Bold),
                    TextEntry::make('url')
                        ->label('Website URL')
                        ->url(fn ($record): string => $record->url)
                        ->openUrlInNewTab(),
                    TextEntry::make('description')
                        ->markdown(),
                    TextEntry::make('status')
                        ->badge()
                        ->color(fn (string $state): string => match ($state) {
                            default => 'gray',
                            1 => 'success',
                            2 => 'success',
                            3 => 'danger',
                        })
                ]),
            Section::make('Site Status')
                ->description('Your sites status.')
                ->icon('heroicon-o-globe-alt')
                ->schema([
                    IconEntry::make('status')
                        ->label("Site Status.")
                        ->boolean(),
                    Actions::make([
                        Action::make('Start / Stop')
                            ->label(fn ($record) => $record->status == 0 ? "Start" : "Stop")
                            ->icon(fn ($record) => $record->status == 0 ? "heroicon-m-play" : "heroicon-m-stop")
                            ->requiresConfirmation()
                            ->action(function ($record) {
                                // $record = $this->record;
                                // Toggle the status code of the record

                                $value = \App\WP\WPApiV2::prepareForPostExtraction($record);

                                if ($value == false) {
                                    Notification::make()
                                        ->title("Sorry Something went wrong please check the logs.")
                                        ->seconds(5)
                                        ->success()
                                        ->send();
                                    return;
                                }

                                // Show a success notification
                                Notification::make()
                                    ->title("Status changed successfully.")
                                    ->seconds(5)
                                    ->success()
                                    ->send();
                            }),
                        // Action::make('reset')
                        //     ->icon('heroicon-m-x-mark')
                        //     ->color('danger')
                        //     ->requiresConfirmation()
                        //     ->action(function ($record) {
                        //         // $resetStars();
                        //     }),
                        Action::make('Start Fetching posts')
                            ->disabled(fn ($record) => $record->status == 0 ? true : false)
                            ->action(function ($record) {
                                \App\WP\WPApiV2::getPosts($record);
                                Notification::make()
                                    ->title('We have started fetching data. All jobs are queued.' . $record->title)
                                    ->seconds(5)
                                    ->success()
                                    ->send();
                            }),
                        Action::make('Publish All')
                            ->form([
                                Select::make('hashnodeId')
                                    ->label('Hashnode Account')
                                    ->options(Setting::query()->pluck('name', 'id'))
                                    ->required(),
                            ])
                            ->action(function (array $data, $record): void {
                                // $record->author()->associate($data['authorId']);
                                // $record->save();
                                // Dispatch the task
                                dispatch(new PublishAllSitePosts($record->id, $data['hashnodeId']));
                                Notification::make()
                                    ->title('Job dispatched successfully. Please refresh the page after some time to get the latest information.')
                                    ->success()
                                    ->send();
                            })->slideOver()
                        // ->visible(fn ($record) => !$record->published),
                    ]),
                ]),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSites::route('/'),
            'create' => Pages\CreateSite::route('/create'),
            'view' => Pages\ViewSite::route('/{record}'),
            'edit' => Pages\EditSite::route('/{record}/edit'),
            'process' => Pages\ProcessSite::route('/{record}/process'),
            'activities' => Pages\LogSitesActivity::route('/{record}/activities')
        ];
    }
}