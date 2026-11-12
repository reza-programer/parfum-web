<?php

namespace App\Http\Controllers;

use App\Exceptions\PaymentException;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Callback Komerce Payment. Tanda tangan (X-Callback-Api-Key) wajib valid, lalu callback hanya
 * dipakai sebagai pemicu: status dan nominal selalu diambil ulang dari API cek status Komerce,
 * karena format payload callback tidak didokumentasikan.
 */
class KomerceCallbackController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $body = $request->getContent();

        if (! $payments->verifyCallback($body, $request->header('X-Callback-Api-Key'))) {
            Log::warning('Komerce callback: tanda tangan tidak valid', ['ip' => $request->ip()]);

            return response()->json(['message' => 'invalid signature'], 401);
        }

        $data = json_decode($body, true);
        $data = is_array($data) ? $data : [];
        $paymentId = $this->field($data, 'payment_id');
        $reference = $this->field($data, 'order_id');

        // Hanya kunci penting yang dicatat (tanpa data customer), untuk memastikan format payload.
        Log::info('Komerce callback diterima', [
            'keys' => array_keys($data),
            'payment_id' => $paymentId,
            'order_id' => $reference,
            'status' => $this->field($data, 'status'),
        ]);

        $payment = $paymentId || $reference
            ? Payment::query()
                ->where(fn ($q) => $q
                    ->when($paymentId, fn ($q) => $q->orWhere('provider_payment_id', $paymentId))
                    ->when($reference, fn ($q) => $q->orWhere('reference', $reference)))
                ->first()
            : null;

        if (! $payment) {
            return response()->json(['message' => 'payment not found'], 404);
        }

        try {
            $payments->refresh($payment, wait: true);
        } catch (PaymentException) {
            return response()->json(['message' => 'status check failed, retry later'], 503);
        }

        return response()->json(['message' => 'ok']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function field(array $data, string $key): ?string
    {
        $value = data_get($data, $key) ?? data_get($data, "data.{$key}");

        return is_scalar($value) && $value !== '' ? (string) $value : null;
    }
}
