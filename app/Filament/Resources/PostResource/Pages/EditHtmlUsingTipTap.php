<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Actions;
use Filament\Forms\Form;
use Filament\Resources\Pages\EditRecord;
use FilamentTiptapEditor\TiptapEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;

class EditHtmlUsingTipTap extends EditRecord
{
    protected static string $resource = PostResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TiptapEditor::make('body.content')
                    ->profile('default')
                    // ->tools([]) // individual tools to use in the editor, overwrites profile
                    ->disk('string') // optional, defaults to config setting
                    ->directory('string or Closure returning a string') // optional, defaults to config setting
                    ->acceptedFileTypes(['array of file types']) // optional, defaults to config setting
                    // ->maxFileSize('integer in KB') // optional, defaults to config setting
                    ->output(TiptapOutput::Html) // optional, change the format for saved data, default is html
                    ->maxContentWidth('full')
                    // ->disableFloatingMenus()
                    // ->disableBubbleMenus()
                    ->required()
            ]);
    }
}
