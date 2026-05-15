<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Jobs\GenerateUsersExportJob;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [

            Actions\CreateAction::make(),

            Actions\Action::make('export')

                ->label('Gerar Relatório')

                ->icon('heroicon-o-arrow-down-tray')

                ->color('success')

                ->requiresConfirmation()

                ->action(function () {

                    cache()->put(
                        'users_export_status',
                        'processing',
                        now()->addHour()
                    );

                    cache()->forget(
                        'users_export_file'
                    );

                    GenerateUsersExportJob::dispatch();

                    Notification::make()

                        ->title('Exportação iniciada')

                        ->body(
                            'O relatório CSV está sendo gerado em background.'
                        )

                        ->success()

                        ->send();
                }),

            Actions\Action::make('download')

                ->label('Baixar Último CSV')

                ->icon('heroicon-o-document-arrow-down')

                ->color('info')

                ->url(fn () => url('/download-export'))

                ->openUrlInNewTab(),
        ];
    }
}