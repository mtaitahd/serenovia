<?php

namespace App\Support\TourGenerator;

use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

final class TourDocuments
{
    public const FIELDS = [
        'tour_ref', 'tour_title', 'day_number', 'day_title', 'tour_type', 'country_code',
        'destination_id', 'category_id', 'start', 'end', 'overnight', 'accommodation_id',
        'transport', 'travel_minutes', 'activities', 'description', 'meals', 'distance',
        'walking_time', 'altitude', 'latitude', 'longitude', 'gallery_ids', 'transport_gallery_ids', 'position',
        'overview', 'highlights', 'inclusions', 'exclusions', 'price_from', 'currency', 'notes',
    ];

    public function read(string $path, string $format): array
    {
        $rows = match ($format) {
            'csv' => $this->csv($path),
            'xlsx' => $this->xlsx($path),
            'docx' => $this->docx($path),
            'pdf' => $this->pdf($path),
            default => throw new RuntimeException('Unsupported document type.'),
        };
        if (!$rows || count($rows) > 500) {
            throw new RuntimeException('Upload a document containing 1 to 500 itinerary rows.');
        }

        return $rows;
    }

    private function csv(string $path): array
    {
        $file = new \SplFileObject($path);
        $file->setCsvControl(',', '"', '');
        $file->setFlags(\SplFileObject::READ_CSV | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $rows = [];
        foreach ($file as $row) {
            $rows[] = $row;
            if (count($rows) > 501) {
                throw new RuntimeException('The CSV exceeds 500 itinerary rows.');
            }
        }
        return $this->map($rows);
    }

    private function map(array $rows): array
    {
        $headers = array_map(fn ($column) => Str::snake(str_replace('-', ' ', trim((string) $column, "\xEF\xBB\xBF \t"))), array_shift($rows) ?? []);
        if (!in_array('day_title', $headers, true) || count(array_unique($headers)) !== count($headers)) {
            throw new RuntimeException('The document needs a unique Day Title column. Use the template headers.');
        }
        $mapped = [];
        foreach ($rows as $index => $row) {
            $record = [];
            foreach ($headers as $column => $field) {
                if (in_array($field, self::FIELDS, true)) {
                    $record[$field] = trim((string) ($row[$column] ?? ''));
                }
            }
            if (array_filter($record, fn ($value) => $value !== '') && !str_starts_with($record['tour_ref'] ?? '', 'EXAMPLE-')) {
                $mapped['Row '.($index + 2)] = $record;
            }
        }
        return $mapped;
    }

    private function xlsx(string $path): array
    {
        $zip = $this->archive($path);
        try {
            $strings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                foreach ($this->xml($zip, 'xl/sharedStrings.xml')->xpath('//*[local-name()="si"]') as $item) {
                    $strings[] = $this->text($item);
                }
            }
            $rows = $this->sheetRows($zip, 'xl/worksheets/sheet1.xml', $strings);
            $mapped = $this->map($rows);
            if ($zip->locateName('xl/worksheets/sheet3.xml') !== false) {
                $workbook = $this->xml($zip, 'xl/workbook.xml');
                $names = array_map(fn ($sheet) => (string) $sheet['name'], $workbook->xpath('//*[local-name()="sheet"]'));
                if (($names[2] ?? '') === 'Tours') {
                    $tourRows = $this->sheetRows($zip, 'xl/worksheets/sheet3.xml', $strings);
                    $headers = array_shift($tourRows);
                    $references = [];
                    foreach ($tourRows as $row) {
                        $fields = array_combine($headers, array_pad(array_slice($row, 0, count($headers)), count($headers), ''));
                        $reference = trim($fields['tour_ref'] ?? '');
                        if ($reference === '' || str_starts_with($reference, 'EXAMPLE-')) {
                            continue;
                        }
                        if (isset($references[$reference])) {
                            throw new RuntimeException('The Tours sheet contains a repeated Tour Ref.');
                        }
                        $references[$reference] = array_intersect_key($fields, array_flip(self::FIELDS));
                    }
                    foreach ($mapped as &$day) {
                        $metadata = $references[$day['tour_ref'] ?? ''] ?? [];
                        $day = array_filter($day, fn ($value) => $value !== '') + $metadata;
                    }
                    unset($day);
                }
            }
            return $mapped;
        } finally {
            $zip->close();
        }
    }

    private function sheetRows(ZipArchive $zip, string $member, array $strings): array
    {
            $sheet = $this->xml($zip, $member);
            $rows = [];
            foreach ($sheet->xpath('//*[local-name()="row"]') as $row) {
                $values = [];
                foreach ($row->xpath('./*[local-name()="c"]') as $cell) {
                    if ($cell->xpath('./*[local-name()="f"]')) {
                        throw new RuntimeException('Convert spreadsheet formulas to values before uploading.');
                    }
                    preg_match('/^([A-Z]{1,2})[0-9]+$/', (string) $cell['r'], $address);
                    if (!$address) {
                        throw new RuntimeException('Invalid spreadsheet cell address.');
                    }
                    $index = 0;
                    foreach (str_split($address[1]) as $letter) {
                        $index = $index * 26 + ord($letter) - 64;
                    }
                    $value = (string) ($cell->xpath('./*[local-name()="v"]')[0] ?? '');
                    $values[$index - 1] = match ((string) $cell['t']) {
                        's' => $strings[(int) $value] ?? '',
                        'inlineStr' => $this->text($cell),
                        default => $value,
                    };
                }
                $rows[] = array_replace(array_fill(0, $values ? max(array_keys($values)) + 1 : 0, ''), $values);
                if (count($rows) > 501) {
                    throw new RuntimeException('A sheet exceeds 500 itinerary rows.');
                }
            }
            return $rows;
    }

    private function docx(string $path): array
    {
        $zip = $this->archive($path);
        try {
            $document = $this->xml($zip, 'word/document.xml');
            $rows = [];
            foreach ($document->xpath('//*[local-name()="tbl"]') as $table) {
                $record = [];
                foreach ($table->xpath('./*[local-name()="tr"]') as $row) {
                    $cells = $row->xpath('./*[local-name()="tc"]');
                    $key = Str::snake($this->text($cells[0] ?? $row));
                    if (isset($cells[1]) && in_array($key, self::FIELDS, true)) {
                        $record[$key] = $this->text($cells[1]);
                    }
                }
                if ($record && !str_starts_with($record['tour_ref'] ?? '', 'EXAMPLE-')) {
                    $rows['Table '.(count($rows) + 1)] = $record;
                }
            }
            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function pdf(string $path): array
    {
        if (file_get_contents($path, false, null, 0, 5) !== '%PDF-') {
            throw new RuntimeException('The file is not a PDF.');
        }
        $process = new Process([config('tour-generator.pdftotext', 'pdftotext'), '-f', '1', '-l', '100', '-enc', 'UTF-8', $path, '-']);
        $process->setTimeout(15);
        try {
            $process->mustRun();
        } catch (\Exception $error) {
            report($error);
            throw new RuntimeException('PDF extraction failed. Check the local Poppler installation or upload a structured template.');
        }
        $text = trim($process->getOutput());
        if ($text === '') {
            throw new RuntimeException('No searchable text was found. A scanned PDF requires OCR before upload.');
        }
        preg_match_all('/^\h*Day\h+(\d+)\b\h*[:.\-–]?\h*([^\r\n]*)\R(.*?)(?=^\h*Day\h+\d+\b|\z)/imsu', $text, $matches, PREG_SET_ORDER);
        $rows = [];
        foreach ($matches as $index => $match) {
            $rows['PDF day '.($index + 1)] = [
                'day_number' => $match[1], 'day_title' => trim($match[2]),
                'description' => trim($match[3]), 'tour_ref' => 'PDF-TOUR',
            ];
        }
        if (!$rows) {
            throw new RuntimeException('No Day 1, Day 2, etc. headings were recognized. Use the Word or Excel template.');
        }
        return $rows;
    }

    private function archive(string $path): ZipArchive
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('Enable the PHP zip extension to process Excel and Word documents.');
        }
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('The document is not a valid Office archive.');
        }
        $total = 0;
        for ($index = 0; $index < $zip->numFiles; $index++) {
            $member = $zip->statIndex($index);
            $total += $member['size'];
            if ($zip->numFiles > 300 || $total > 12000000 || preg_match('/vbaProject\.bin$/i', $member['name'])) {
                $zip->close();
                throw new RuntimeException('The document exceeds extraction limits or contains macros.');
            }
        }
        return $zip;
    }

    private function xml(ZipArchive $zip, string $name): \SimpleXMLElement
    {
        $raw = $zip->getFromName($name);
        if (!$raw || stripos($raw, '<!DOCTYPE') !== false || stripos($raw, '<!ENTITY') !== false) {
            throw new RuntimeException('The document structure is invalid or unsafe.');
        }
        $xml = @simplexml_load_string($raw, \SimpleXMLElement::class, LIBXML_NONET);
        if ($xml === false) {
            throw new RuntimeException('The document contains invalid XML.');
        }
        return $xml;
    }

    private function text(\SimpleXMLElement $node): string
    {
        return implode('', array_map(fn ($text) => (string) $text, $node->xpath('.//*[local-name()="t"]') ?: []));
    }

    public function download(string $format): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tour-template-');
        try {
            $zip = new ZipArchive;
            if ($zip->open($path, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('Could not create the template file.');
            }
            $namespace = 'http://schemas.openxmlformats.org';
            $spreadsheet = $format === 'xlsx';
            $target = $spreadsheet ? 'xl/workbook.xml' : 'word/document.xml';
            $type = $spreadsheet ? 'spreadsheetml.sheet' : 'wordprocessingml.document';
            $zip->addFromString('[Content_Types].xml', "<Types xmlns=\"$namespace/package/2006/content-types\"><Default Extension=\"rels\" ContentType=\"application/vnd.openxmlformats-package.relationships+xml\"/><Default Extension=\"xml\" ContentType=\"application/xml\"/><Override PartName=\"/$target\" ContentType=\"application/vnd.openxmlformats-officedocument.$type.main+xml\"/>"
                .($spreadsheet ? '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/worksheets/sheet2.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>' : '').'</Types>');
            $zip->addFromString('_rels/.rels', "<Relationships xmlns=\"$namespace/package/2006/relationships\"><Relationship Id=\"rId1\" Type=\"$namespace/officeDocument/2006/relationships/officeDocument\" Target=\"$target\"/></Relationships>");
            $sample = ['tour_ref' => 'EXAMPLE-01', 'day_number' => '1', 'day_title' => 'Forest walk', 'tour_type' => 'safari', 'country_code' => 'TZ'];
            $instructions = 'Use one row or table per day. For a complete tour, repeat the Tour Ref and number days consecutively. Leave Tour Ref empty for a reusable block. Use published catalogue IDs, separate activities and gallery IDs with |, and replace EXAMPLE-01 before importing.';
            if ($spreadsheet) {
                $xmlns = "$namespace/spreadsheetml/2006/main";
                $names = ['Day-by-Day', 'Instructions', 'Tours', 'Accommodations'];
                $sheets = $relationships = $overrides = '';
                foreach ($names as $index => $name) {
                    $id = $index + 1;
                    $sheets .= "<sheet name=\"$name\" sheetId=\"$id\" r:id=\"rId$id\"/>";
                    $relationships .= "<Relationship Id=\"rId$id\" Type=\"$namespace/officeDocument/2006/relationships/worksheet\" Target=\"worksheets/sheet$id.xml\"/>";
                    if ($id > 2) {
                        $overrides .= "<Override PartName=\"/xl/worksheets/sheet$id.xml\" ContentType=\"application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml\"/>";
                    }
                }
                $contentTypes = $zip->getFromName('[Content_Types].xml');
                $zip->addFromString('[Content_Types].xml', str_replace('</Types>', $overrides.'</Types>', $contentTypes));
                $zip->addFromString($target, "<workbook xmlns=\"$xmlns\" xmlns:r=\"$namespace/officeDocument/2006/relationships\"><sheets>$sheets</sheets></workbook>");
                $zip->addFromString('xl/_rels/workbook.xml.rels', "<Relationships xmlns=\"$namespace/package/2006/relationships\">$relationships</Relationships>");
                $rowsBySheet = [
                    1 => [self::FIELDS, array_map(fn ($field) => $sample[$field] ?? '', self::FIELDS)],
                    2 => [[$instructions], ['Use the Accommodations sheet to look up existing properties. New properties are managed in the admin accommodation catalogue.']],
                    3 => [['tour_ref', 'tour_title', 'category_id', 'country_code', 'destination_id', 'overview', 'highlights', 'inclusions', 'exclusions', 'price_from', 'currency'], ['EXAMPLE-01', 'Forest walk']],
                    4 => [['accommodation_id', 'name', 'destination', 'tier', 'description']],
                ];
                foreach (\App\Models\Accommodation::with('destination')->where('status', 'published')->orderBy('name')->limit(500)->get() as $stay) {
                    $rowsBySheet[4][] = [(string) $stay->id, $stay->name, $stay->destination?->name ?? '', $stay->tier, strip_tags($stay->description ?? '')];
                }
                foreach ($rowsBySheet as $sheet => $rows) {
                    $body = '';
                    foreach ($rows as $row => $values) {
                        $number = $row + 1;
                        $body .= "<row r=\"$number\">";
                        foreach ($values as $index => $value) {
                            $column = ($index >= 26 ? 'A' : '').chr(65 + $index % 26);
                            $body .= "<c r=\"$column$number\" t=\"inlineStr\"><is><t>".$this->escape($value).'</t></is></c>';
                        }
                        $body .= '</row>';
                    }
                    $zip->addFromString("xl/worksheets/sheet$sheet.xml", "<worksheet xmlns=\"$xmlns\"><sheetViews><sheetView workbookViewId=\"0\"><pane ySplit=\"1\" topLeftCell=\"A2\" state=\"frozen\"/></sheetView></sheetViews><sheetData>$body</sheetData></worksheet>");
                }
            } else {
                $paragraph = fn ($value) => '<w:p><w:r><w:t>'.$this->escape($value).'</w:t></w:r></w:p>';
                $body = $paragraph('Serenovia Tour Itinerary').$paragraph($instructions).'<w:tbl><w:tblPr/><w:tblGrid><w:gridCol w:w="4000"/><w:gridCol w:w="5000"/></w:tblGrid>';
                foreach (self::FIELDS as $field) {
                    $body .= '<w:tr><w:tc>'.$paragraph($field).'</w:tc><w:tc>'.$paragraph($sample[$field] ?? '').'</w:tc></w:tr>';
                }
                $zip->addFromString($target, "<w:document xmlns:w=\"$namespace/wordprocessingml/2006/main\"><w:body>$body</w:tbl><w:sectPr/></w:body></w:document>");
            }
            if (!$zip->close()) {
                throw new RuntimeException('Could not save the template file.');
            }
            return file_get_contents($path);
        } finally {
            @unlink($path);
        }
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
