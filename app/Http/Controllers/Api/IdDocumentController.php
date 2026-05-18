<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\IdDocumentParsingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class IdDocumentController extends Controller
{
    public function parse(Request $request, IdDocumentParsingService $parser): JsonResponse
    {
        $validated = $request->validate([
            'id_document' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        $parsed = $parser->parse($validated['id_document']);

        $name = $parsed['name'] ?? null;
        $dob = $parsed['date_of_birth'] ?? null;

        return response()->json([
            'name' => $name,
            'date_of_birth' => $dob,
            'parsed' => filled($name) || filled($dob),
            'message' => filled($name) || filled($dob)
                ? null
                : 'Could not read a clear name or date of birth from this image. Use a sharper photo or type your details manually.',
        ]);
    }
}
