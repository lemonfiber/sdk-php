<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Exception;

/**
 * lemonfiber admitted the caller and declined this request anyway.
 *
 * Answered `401` or `403` with a code other than the credential's own: a key
 * that may not call this action, or a member asking for something only the
 * operator may. The credential works; this request is not one it may make.
 */
final class Declined extends RequestFailed {}
