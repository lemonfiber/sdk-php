<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * lemonfiber took the request and could not carry it out.
 *
 * Every status no other family claims, `500` and `503` among them: the command
 * ran and stopped on a problem, which the refusal's document describes where
 * there is one.
 */
final class Failed extends RequestFailed {}
