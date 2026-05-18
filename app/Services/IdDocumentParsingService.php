<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

class IdDocumentParsingService
{
    /** @var array<string, int> */
    private const MONTHS = [
        'jan' => 1, 'january' => 1,
        'feb' => 2, 'february' => 2,
        'mar' => 3, 'march' => 3,
        'apr' => 4, 'april' => 4,
        'may' => 5,
        'jun' => 6, 'june' => 6,
        'jul' => 7, 'july' => 7,
        'aug' => 8, 'august' => 8,
        'sep' => 9, 'sept' => 9, 'september' => 9,
        'oct' => 10, 'october' => 10,
        'nov' => 11, 'november' => 11,
        'dec' => 12, 'december' => 12,
    ];

    /**
     * @return array{name?: string, date_of_birth?: string|null}
     */
    public function parse(UploadedFile $file): array
    {
        $url = (string) (config('services.id_ocr.url') ?? '');
        $token = (string) (config('services.id_ocr.token') ?? '');

        if ($url === '') {
            return [];
        }

        $parsedText = str_contains($url, 'ocr.space')
            ? $this->fetchOcrSpaceText($file, $url, $token)
            : $this->fetchGenericOcrText($file, $url, $token);

        if ($parsedText === '') {
            return [];
        }

        $name = $this->extractNameFromText($parsedText);
        $dob = $this->extractDateOfBirthFromText($parsedText);

        if ($name === null && $dob === null) {
            return [];
        }

        return array_filter([
            'name' => $name,
            'date_of_birth' => $dob,
        ], fn ($value) => $value !== null && $value !== '');
    }

    private function extractNameFromText(string $text): ?string
    {
        if (preg_match('/(?:name|full\s*name|nom|الاسم)\s*[:\-]?\s*([^\r\n]{3,80})/iu', $text, $matches) === 1) {
            $candidate = $this->cleanNameCandidate($matches[1]);
            if ($candidate !== null) {
                return $candidate;
            }
        }

        $lines = array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $text) ?: []),
            fn (string $line) => $line !== ''
        ));

        for ($i = 0; $i < count($lines); $i++) {
            if (preg_match('/^(?:name|nom|الاسم)\s*:?\s*$/iu', $lines[$i]) && isset($lines[$i + 1])) {
                $combined = $this->combineNameLines(array_slice($lines, $i + 1, 4));
                if ($combined !== null) {
                    return $combined;
                }
            }
        }

        for ($i = 0; $i < count($lines) - 1; $i++) {
            $combined = $this->combineNameLines(array_slice($lines, $i, 3));
            if ($combined !== null && ! $this->looksLikeNoiseLine($lines[$i])) {
                $nextLine = $lines[$i + 1] ?? '';
                if ($this->isLikelyNamePart($lines[$i]) && $this->isLikelyNamePart($nextLine)) {
                    return $combined;
                }
            }
        }

        foreach ($lines as $line) {
            if ($this->isLikelyFullNameLine($line)) {
                return $this->cleanNameCandidate($line);
            }
        }

        return null;
    }

    /**
     * @param  array<int, string>  $lines
     */
    private function combineNameLines(array $lines): ?string
    {
        $parts = [];

        foreach ($lines as $line) {
            if ($this->looksLikeNoiseLine($line) || $this->extractDateOfBirthFromText($line) !== null) {
                break;
            }

            if ($this->isLikelyNamePart($line)) {
                $parts[] = $line;
            } elseif ($parts !== []) {
                break;
            }

            if (count($parts) >= 4) {
                break;
            }
        }

        if ($parts === []) {
            return null;
        }

        return $this->cleanNameCandidate(implode(' ', $parts));
    }

    private function isLikelyNamePart(string $line): bool
    {
        if ($this->looksLikeNoiseLine($line)) {
            return false;
        }

        return preg_match('/^[\p{L}][\p{L}\'.-]{0,39}$/u', $line) === 1
            || preg_match('/^[\p{L}][\p{L}\'.-]*(?:\s+[\p{L}][\p{L}\'.-]*){0,3}$/u', $line) === 1;
    }

    private function isLikelyFullNameLine(string $line): bool
    {
        if ($this->looksLikeNoiseLine($line)) {
            return false;
        }

        return preg_match('/^[\p{L}][\p{L}\'.-]*(?:\s+[\p{L}][\p{L}\'.-]*){1,4}$/u', $line) === 1;
    }

    private function looksLikeNoiseLine(string $line): bool
    {
        if (preg_match('/^(pass|citizen|card|proof|age|npc|sia|security|expires|expiry|\d{2}\+|\d{4}\s*\d{4})/iu', $line)) {
            return true;
        }

        return preg_match('/\d{3,}/', $line) === 1;
    }

    private function cleanNameCandidate(string $raw): ?string
    {
        $name = preg_replace('/\s+/', ' ', trim($raw));
        $name = preg_replace('/\s+(date|dob|birth|sex|gender|expires|expiry).*$/iu', '', $name) ?? $name;

        if ($name === '' || ! preg_match('/[\p{L}]{2,}/u', $name)) {
            return null;
        }

        if (preg_match('/^[\p{L}][\p{L}\'.-]*(?:\s+[\p{L}][\p{L}\'.-]*){0,4}$/u', $name) !== 1) {
            return null;
        }

        return $name;
    }

    private function extractDateOfBirthFromText(string $text): ?string
    {
        if (preg_match('/(?:date\s*of\s*birth|dob|birth\s*date|born|تاريخ\s*الميلاد)\s*[:\-]?\s*([^\r\n]{6,30})/iu', $text, $matches) === 1) {
            $parsed = $this->parseDateToken(trim($matches[1]));
            if ($parsed !== null) {
                return $parsed;
            }
        }

        if (preg_match('/\b(\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4})\b/i', $text, $matches) === 1) {
            $parsed = $this->parseDateToken($matches[1]);
            if ($parsed !== null) {
                return $parsed;
            }
        }

        if (preg_match('/\b(\d{2}[\/.\-]\d{2}[\/.\-]\d{4})\b/', $text, $matches) === 1) {
            return $this->normalizeNumericDate($matches[1]);
        }

        if (preg_match('/\b(\d{4}-\d{2}-\d{2})\b/', $text, $matches) === 1) {
            return $this->parseDateToken($matches[1]);
        }

        return null;
    }

    private function parseDateToken(string $token): ?string
    {
        $token = trim($token);

        if (preg_match('/^(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})$/i', $token, $matches) === 1) {
            $day = (int) $matches[1];
            $month = self::MONTHS[strtolower($matches[2])] ?? null;
            $year = (int) $matches[3];

            if ($month !== null && checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $token, $matches) === 1) {
            $year = (int) $matches[1];
            $month = (int) $matches[2];
            $day = (int) $matches[3];

            return checkdate($month, $day, $year) ? sprintf('%04d-%02d-%02d', $year, $month, $day) : null;
        }

        return $this->normalizeNumericDate($token);
    }

    private function normalizeNumericDate(string $rawDate): ?string
    {
        $date = str_replace(['/', '.'], '-', trim($rawDate));
        $parts = explode('-', $date);
        if (count($parts) !== 3) {
            return null;
        }

        [$d, $m, $y] = $parts;
        if (! checkdate((int) $m, (int) $d, (int) $y)) {
            return null;
        }

        return sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
    }

    private function fetchOcrSpaceText(UploadedFile $file, string $url, string $token): string
    {
        $configured = (string) (config('services.id_ocr.language') ?: 'eng');
        $languages = array_values(array_filter(array_map(
            'trim',
            preg_split('/\s*,\s*/', $configured) ?: ['eng']
        )));

        if ($languages === []) {
            $languages = ['eng'];
        }

        $chunks = [];

        foreach ($languages as $language) {
            $text = $this->requestOcrSpace($file, $url, $token, $language);
            if ($text !== '') {
                $chunks[] = $text;
            }
        }

        if ($chunks === [] && ! in_array('eng', $languages, true)) {
            $fallback = $this->requestOcrSpace($file, $url, $token, 'eng');
            if ($fallback !== '') {
                $chunks[] = $fallback;
            }
        }

        return trim(implode("\n", array_unique($chunks)));
    }

    private function requestOcrSpace(UploadedFile $file, string $url, string $token, string $language): string
    {
        $stream = null;

        try {
            $stream = fopen($file->getRealPath(), 'r');
            if ($stream === false) {
                return '';
            }

            $response = Http::timeout(45)
                ->attach('file', $stream, $file->getClientOriginalName())
                ->post($url, [
                    'apikey' => $token,
                    'language' => $language,
                    'isOverlayRequired' => 'false',
                    'OCREngine' => '2',
                    'detectOrientation' => 'true',
                    'scale' => 'true',
                ]);
        } catch (Throwable) {
            return '';
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $response->successful()) {
            return '';
        }

        $data = $response->json();

        if ((int) ($data['OCRExitCode'] ?? 0) !== 1) {
            return '';
        }

        return trim((string) ($data['ParsedResults'][0]['ParsedText'] ?? ''));
    }

    private function fetchGenericOcrText(UploadedFile $file, string $url, string $token): string
    {
        $stream = null;

        try {
            $request = Http::timeout(45);
            if ($token !== '') {
                $request = $request->withToken($token);
            }

            $stream = fopen($file->getRealPath(), 'r');
            if ($stream === false) {
                return '';
            }

            $response = $request
                ->attach('file', $stream, $file->getClientOriginalName())
                ->post($url);
        } catch (Throwable) {
            return '';
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $response->successful()) {
            return '';
        }

        $data = $response->json();

        return trim((string) ($data['text'] ?? $data['ParsedResults'][0]['ParsedText'] ?? ''));
    }
}
