<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * Something answered, and the answer cannot be used.
 *
 * {@see ApiVersionMismatch}, {@see UnreadableResponse} and {@see UnexpectedKind}:
 * the request reached a server and what came back is not an envelope this
 * client reads as the one asked for. Each means the same to a caller, which is
 * that this client and that server do not agree on what an answer is.
 */
interface AnswerUnusable extends Problem {}
