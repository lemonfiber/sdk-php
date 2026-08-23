<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: v0.9.0-unreleased, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every kind the contract describes, and the only ones this package reads.
 */
enum Kind: string
{
    case Error = 'error';
    case Log = 'log';
    case Setup = 'setup';
    case Walkthrough = 'walkthrough';
    case Watch = 'watch';
    case Word = 'word';
}
