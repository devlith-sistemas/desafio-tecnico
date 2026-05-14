<?php

namespace App\Exports\Students;

use App\Models\StudentExport;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;

class StudentExportNotifier
{
    public function completed(StudentExport $export): void
    {
        $user = $export->requestedBy;

        if (! $user) {
            return;
        }

        Notification::make()
            ->title('Exportação de alunos concluída')
            ->body(number_format($export->rows_processed, 0, ',', '.').' alunos foram exportados para Excel.')
            ->success()
            ->actions([
                Action::make('download')
                    ->label('Baixar Excel')
                    ->button()
                    ->markAsRead()
                    ->url(route('student-exports.download', $export), shouldOpenInNewTab: true),
            ])
            ->sendToDatabase($user, isEventDispatched: true);
    }

    public function failed(StudentExport $export): void
    {
        $user = $export->requestedBy;

        if (! $user) {
            return;
        }

        Notification::make()
            ->title('A exportação de alunos falhou')
            ->body('Tente novamente em alguns instantes. Se o problema persistir, verifique os logs da aplicação.')
            ->danger()
            ->sendToDatabase($user, isEventDispatched: true);
    }
}
