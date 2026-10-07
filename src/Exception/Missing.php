<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * What the request named is not there.
 *
 * Answered `404`: a service, form, archive or other thing the request named that
 * this stack does not hold.
 */
final class Missing extends RequestFailed {}
