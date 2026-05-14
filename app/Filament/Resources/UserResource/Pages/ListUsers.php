<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Actions\Students\RequestStudentsExport;
use App\Filament\Resources\UserResource;
use App\Models\StudentExport;
use App\Models\User;
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
            Actions\Action::make('exportStudents')
                ->label('Exportar Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Exportar alunos para Excel')
                ->modalDescription('A planilha será gerada em segundo plano. Você pode continuar usando o painel e receberá uma notificação quando o arquivo estiver pronto.')
                ->action(function (): void {
                    $user = auth()->user();

                    if (! $user instanceof User) {
                        return;
                    }

                    $export = app(RequestStudentsExport::class)($user);

                    Notification::make()
                        ->title('Exportação enviada para a fila')
                        ->body('O arquivo '.$export->file_name.' será disponibilizado assim que o processamento terminar.')
                        ->success()
                        ->send();
                }),
            Actions\Action::make('studentExports')
                ->label('Últimas exportações')
                ->icon('heroicon-o-clock')
                ->modalHeading('Últimas exportações')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar')
                ->modalContent(fn () => view('filament.resources.user-resource.pages.student-exports-modal', [
                    'exports' => StudentExport::query()
                        ->where('requested_by_user_id', auth()->id())
                        ->latest()
                        ->limit(10)
                        ->get(),
                ])),
        ];
    }
}
