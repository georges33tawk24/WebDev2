<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Throwable;

class IdDocumentParsingService
{
    /**
     * Calls an external ID parsing/OCR API and maps the response to user fields.
     *
     * If the API is not configured (no URL), it returns an empty array.
     *
     * @return array{name?: string, date_of_birth?: string|null}
     */
    public function parse(UploadedFile $file): array
    {
        $url = (string) (config('services.id_ocr.url') ?? '');
        $token = (string) (config('services.id_ocr.token') ?? '');

        if ($url === '') {
            return [];
        }

        $stream = null;

        try {
            $request = Http::timeout(30);

            $stream = fopen($file->getRealPath(), 'r');
            if ($stream === false) {
                return [];
            }

            if (str_contains($url, 'ocr.space')) {
                $response = $request
                    ->attach('file', $stream, $file->getClientOriginalName())
                    ->post($url, [
                        'apikey' => $token,
                        'language' => 'eng',
                        'isOverlayRequired' => 'false',
                    ]);
            } else {
                if ($token !== '') {
                    $request = $request->withToken($token);
                }

                $response = $request
                    ->attach('file', $stream, $file->getClientOriginalName())
                    ->post($url);
            }

        } catch (Throwable) {
            return [];
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        if (! $response->successful()) {
            return [];
        }

        $data = $response->json();

        $name = $data['name']
            ?? $data['full_name']
            ?? $data['fullName']
            ?? null;

        $dob = $data['date_of_birth']
            ?? $data['dob']
            ?? $data['birth_date']
            ?? null;

        if (($name === null || $dob === null) && isset($data['ParsedResults'][0]['ParsedText'])) {
            $parsedText = (string) $data['ParsedResults'][0]['ParsedText'];
            $name ??= $this->extractNameFromText($parsedText);
            $dob ??= $this->extractDateOfBirthFromText($parsedText);
        }

        return [
            'name' => $name,
            'date_of_birth' => $dob,
        ];
    }

    private function extractNameFromText(string $text): ?string
    {
        if (preg_match('/(?:name|full\\s*name)\\s*[:\\-]\\s*([A-Za-z\\s]{3,60})/i', $text, $matches) === 1) {
            return trim($matches[1]);
        }

        $lines = preg_split('/\\r\\n|\\r|\\n/', $text) ?: [];
        foreach ($lines as $line) {
            $candidate = trim($line);
            if (preg_match('/^[A-Za-z]+(?:\\s+[A-Za-z]+){1,4}$/', $candidate) === 1) {
                return $candidate;
            }
        }

        return null;
    }

    private function extractDateOfBirthFromText(string $text): ?string
    {
        if (preg_match('/(?:date\\s*of\\s*birth|dob)\\s*[:\\-]\\s*([0-9]{2}[\\/-][0-9]{2}[\\/-][0-9]{4})/i', $text, $matches) === 1) {
            return $this->normalizeDate($matches[1]);
        }

        if (preg_match('/\\b([0-9]{2}[\\/-][0-9]{2}[\\/-][0-9]{4})\\b/', $text, $matches) === 1) {
            return $this->normalizeDate($matches[1]);
        }

        return null;
    }

    private function normalizeDate(string $rawDate): ?string
    {
        $date = str_replace('/', '-', trim($rawDate));
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
}
