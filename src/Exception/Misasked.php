<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * The request was not one lemonfiber can act on as asked.
 *
 * Answered `400`: an action it does not offer, an argument it does not know, or
 * a value it will not take. Asking again unchanged is answered the same way.
 */
final class Misasked extends RequestFailed {}
