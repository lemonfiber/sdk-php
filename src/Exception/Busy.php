<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * The request collides with something already under way or already true.
 *
 * Answered `409`: other work holds what this needs, or the stack is already in
 * the state asked for. Asking again once that work is over may be answered
 * differently.
 */
final class Busy extends RequestFailed {}
