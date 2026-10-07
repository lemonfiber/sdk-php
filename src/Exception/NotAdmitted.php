<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * lemonfiber does not admit the credential the request carried.
 *
 * Answered `401` or `403` with the code that says so, with no code this client
 * knows, or with no sentence. A run mints its token once, so asking again with
 * the same one is answered the same way: the remedy is the token the run
 * printed, a new session, or a key the operator minted.
 */
final class NotAdmitted extends RequestFailed {}
