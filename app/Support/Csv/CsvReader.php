<?php

namespace App\Support\Csv;

use Illuminate\Http\UploadedFile;
use RuntimeException;

class CsvReader
{
    /**
     * Read a CSV upload into associative rows keyed by the header row.
     *
     * @return array<int, array<string, string>>
     */
    public static function read(UploadedFile $file): array
    {
        $handle = fopen($file->getRealPath(), 'rb');

        if ($handle === false) {
            throw new RuntimeException('File tidak dapat dibaca.');
        }

        $header = fgetcsv($handle, escape: '\\');

        if ($header === false || $header === [null]) {
            fclose($handle);

            throw new RuntimeException('File CSV kosong atau tanpa header.');
        }

        $header = array_map(
            fn ($column) => strtolower(trim(str_replace("\u{FEFF}", '', (string) $column))),
            $header,
        );

        $rows = [];
        $line = 1;

        while (($values = fgetcsv($handle, escape: '\\')) !== false) {
            $line++;

            if ($values === [null]) {
                continue;
            }

            $row = ['__line' => (string) $line];

            foreach ($header as $index => $column) {
                $row[$column] = trim((string) ($values[$index] ?? ''));
            }

            $rows[] = $row;
        }

        fclose($handle);

        return $rows;
    }
}
