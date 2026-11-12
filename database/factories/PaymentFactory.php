<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'reference' => 'INV-'.Str::upper(Str::random(10)).'-1',
            'provider_payment_id' => 'KPAY-'.Str::lower(Str::random(4)).'/KM/2026',
            'payment_type' => Payment::TYPE_VA,
            'channel_code' => 'BCA',
            'bank_name' => 'Bank Central Asia',
            'va_number' => fake()->numerify('3816599#########'),
            'amount' => 100000,
            'status' => PaymentStatus::Pending,
            'expires_at' => now()->addDay(),
        ];
    }

    public function qris(): static
    {
        return $this->state([
            'payment_type' => Payment::TYPE_QRIS,
            'channel_code' => null,
            'bank_name' => null,
            'va_number' => null,
            'qr_string' => '00020101021226670016COM.NOBUBANK.WWW01189360050300000488140214'.fake()->numerify('##########'),
            'expires_at' => now()->addMinutes(5),
        ]);
    }

    public function status(PaymentStatus $status): static
    {
        return $this->state(['status' => $status]);
    }
}
