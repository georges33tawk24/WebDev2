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

        return response()->json([
            'name' => $parsed['name'] ?? null,
            'date_of_birth' => $parsed['date_of_birth'] ?? null,
            'parsed' => $parsed !== [],
        ]);
    }
}
