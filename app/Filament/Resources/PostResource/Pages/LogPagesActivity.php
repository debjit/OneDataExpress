<?php

namespace App\Filament\Resources\PostResource\Pages;

use App\Filament\Resources\PostResource;
use Filament\Resources\Pages\Page;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class LogPagesActivity extends ListActivities
{
    protected static string $resource = PostResource::class;

    // protected static string $view = 'filament.resources.post-resource.pages.log-pages-activity';
}
