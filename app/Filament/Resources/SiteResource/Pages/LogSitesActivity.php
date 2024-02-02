<?php

namespace App\Filament\Resources\SiteResource\Pages;

use App\Filament\Resources\SiteResource;
use Filament\Resources\Pages\Page;
use pxlrbt\FilamentActivityLog\Pages\ListActivities;

class LogSitesActivity extends ListActivities
{
    protected static string $resource = SiteResource::class;

    // public function canRestore()
    // {
    //     return false;
    // }
}
