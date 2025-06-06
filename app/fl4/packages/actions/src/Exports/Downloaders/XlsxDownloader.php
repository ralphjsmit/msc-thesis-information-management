<?php

namespace Filament\Actions\Exports\Downloaders;

use Filament\Actions\Exports\Downloaders\Contracts\Downloader;
use Filament\Actions\Exports\Models\Export;
use League\Csv\Reader as CsvReader;
use League\Csv\Statement;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class XlsxDownloader implements Downloader
{
    public function __invoke(Export $export): StreamedResponse
    {
        $disk = $export->getFileDisk();
        $directory = $export->getFileDirectory();

        if (! $disk->exists($directory)) {
            abort(404);
        }

        $fileName = $export->file_name . '.xlsx';

        if ($disk->exists($filePath = $directory . DIRECTORY_SEPARATOR . $fileName)) {
            $response = $disk->download($filePath);

            if (ob_get_length() > 0) {
                ob_end_clean();
            }

            $response->headers->set('X-Vapor-Base64-Encode', 'True');

            return $response;
        }

        $writer = app(Writer::class);

        $csvDelimiter = $export->exporter::getCsvDelimiter();

        $writeRowsFromFile = function (string $file) use ($csvDelimiter, $disk, $writer): void {
            $csvReader = CsvReader::createFromStream($disk->readStream($file));
            $csvReader->setDelimiter($csvDelimiter);
            $csvResults = (new Statement)->process($csvReader);

            foreach ($csvResults->getRecords() as $row) {
                $writer->addRow(Row::fromValues($row));
            }
        };

        return response()->streamDownload(function () use ($disk, $directory, $fileName, $writer, $writeRowsFromFile): void {
            $writer->openToBrowser($fileName);

            $writeRowsFromFile($directory . DIRECTORY_SEPARATOR . 'headers.csv');

            foreach ($disk->files($directory) as $file) {
                if (str($file)->endsWith('headers.csv')) {
                    continue;
                }

                if (! str($file)->endsWith('.csv')) {
                    continue;
                }

                $writeRowsFromFile($file);
            }

            $writer->close();
        }, $fileName, [
            'Content-Type' => 'application/vnd.ms-excel',
        ]);
    }
}
