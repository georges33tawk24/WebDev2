<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\StripePaymentService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripePaymentService $stripe): Response
    {
        $handled = $stripe->handleWebhook(
            $request->getContent(),
            $request->header('Stripe-Signature'),
        );

        return response()->json(['received' => true], $handled ? 200 : 400);
    }
}
