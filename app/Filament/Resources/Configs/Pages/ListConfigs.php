<?php

namespace App\Filament\Resources\Configs\Pages;

use App\Filament\Resources\Configs\ConfigResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListConfigs extends ListRecords
{
    protected static string $resource = ConfigResource::class;

    protected function getHeaderActions(): array
    {
        // Oculta o CreateAction se já existir pelo menos um registro
        if (\App\Models\Config::query()->exists()) {
            return [];
        }
        
        return [
            CreateAction::make(),
        ];
    }
}
