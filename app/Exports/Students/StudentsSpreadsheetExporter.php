<?php

namespace App\Exports\Students;

use App\Models\StudentExport;
use App\Support\BrazilianDocumentFormatter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Writer;
use Throwable;

class StudentsSpreadsheetExporter
{
    private const HEADINGS = [
        'Nome',
        'E-mail',
        'Data de Nascimento',
        'Range de Escolaridade',
        'Escola',
        'CPF',
        'RG',
        'Logradouro',
        'CEP',
        'Aprovações',
        'Reprovações',
    ];

    public function __construct(
        private readonly StudentExportRowQuery $rowQuery,
    ) {}

    public function write(StudentExport $export): int
    {
        $tempDirectory = storage_path('app/temp/student-exports');
        File::ensureDirectoryExists($tempDirectory);

        $tempPath = $tempDirectory.'/'.Str::orderedUuid().'.xlsx';
        $writer = new Writer();
        $writerOpened = false;

        try {
            $writer->openToFile($tempPath);
            $writerOpened = true;

            $writer->addRow(Row::fromValues(self::HEADINGS, (new Style())->setFontBold()->setShouldWrapText()));

            $rowsProcessed = 0;
            $lastUserId = 0;
            $chunkSize = max(100, (int) config('student-export.chunk_size', 1000));

            while (true) {
                $studentIds = $this->rowQuery->nextStudentIds($lastUserId, $chunkSize);

                if ($studentIds->isEmpty()) {
                    break;
                }

                $spreadsheetRows = $this->rowQuery
                    ->rowsForStudentIds($studentIds)
                    ->map(fn (object $row): Row => Row::fromValues($this->mapRow($row)))
                    ->all();

                if ($spreadsheetRows !== []) {
                    $writer->addRows($spreadsheetRows);
                    $rowsProcessed += count($spreadsheetRows);

                    $export->forceFill(['rows_processed' => $rowsProcessed])->save();
                }

                $lastUserId = (int) $studentIds->last();
            }

            $writer->close();
            $writerOpened = false;

            $this->storeFile($export, $tempPath);

            return $rowsProcessed;
        } finally {
            if ($writerOpened) {
                try {
                    $writer->close();
                } catch (Throwable) {
                    //
                }
            }

            File::delete($tempPath);
        }
    }

    /**
     * @return array<int, int|string>
     */
    private function mapRow(object $row): array
    {
        return [
            (string) $row->name,
            (string) $row->email,
            $this->formatDate($row->birth_date),
            $this->formatEducationRange($row->first_enrollment_year, $row->last_enrollment_year),
            (string) ($row->school_name ?? ''),
            BrazilianDocumentFormatter::cpf($row->cpf),
            BrazilianDocumentFormatter::rg($row->rg),
            (string) ($row->street_address ?? ''),
            BrazilianDocumentFormatter::cep($row->zip_code),
            (int) $row->approvals,
            (int) $row->failures,
        ];
    }

    private function formatDate(mixed $date): string
    {
        if (blank($date)) {
            return '';
        }

        return substr((string) $date, 0, 10);
    }

    private function formatEducationRange(int|string|null $firstYear, int|string|null $lastYear): string
    {
        if (blank($firstYear) || blank($lastYear)) {
            return '';
        }

        return "{$firstYear}-{$lastYear}";
    }

    private function storeFile(StudentExport $export, string $tempPath): void
    {
        $stream = fopen($tempPath, 'rb');

        try {
            Storage::disk($export->disk)->put($export->path, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }
}
