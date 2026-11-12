<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Pesan ditampilkan ke customer, jadi selalu bahasa Indonesia. Detail error API hanya masuk log.
 */
class PaymentException extends RuntimeException {}
