<?php

namespace App\Filament\Resources;

use App\Enums\PostStatus;
use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Post;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Components\Tab;
use Filament\Support\Markdown;
use Filament\Tables\Columns\IconColumn;

// use Illuminate\Database\Eloquent\Builder;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('title'),
                // TextInput::make('body.content')->html(),
                // Textarea::make('body.content')->rows(10)->readonly(),
                MarkdownEditor::make('output')
                // ->columnSpan(2),

                // TextInput::make('body')->richtec,
            ])->columns(1);
    }
    // User can not create a new record/entry
    public static function canCreate(): bool
    {
        return false;
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'queue' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 0)),
            'processed' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 1)),
            'error' => Tab::make()
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', 2)),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('status'),
                TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('site.title')
                    ->searchable()
                    ->sortable(),

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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'view' => Pages\ViewPost::route('/{record}'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
            'edit-tiptap' => Pages\EditHtmlUsingTipTap::route('/{record}/edit/tiptap'),
            'activities' => Pages\LogPagesActivity::route('/{record}/activities')
        ];
    }
}
