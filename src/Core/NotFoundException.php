<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Thrown anywhere during request handling; the front controller turns it into a 404 response.
 */
final class NotFoundException extends RuntimeException
{
}
