<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: b0101f0cc19b008d6a225cdc07515b6982fa4cc0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every kind the contract describes, and the only ones this package reads.
 */
enum Kind: string
{
    case Config = 'config';
    case Dashboard = 'dashboard';
    case Doctor = 'doctor';
    case Error = 'error';
    case Forms = 'forms';
    case Household = 'household';
    case Job = 'job';
    case Lifecycle = 'lifecycle';
    case Log = 'log';
    case Music = 'music';
    case Preview = 'preview';
    case Pull = 'pull';
    case Quality = 'quality';
    case Reset = 'reset';
    case Seed = 'seed';
    case Setup = 'setup';
    case Start = 'start';
    case Status = 'status';
    case Stuck = 'stuck';
    case Trace = 'trace';
    case Upgrade = 'upgrade';
    case Version = 'version';
    case Walkthrough = 'walkthrough';
    case Watch = 'watch';
    case Wizard = 'wizard';
    case Word = 'word';
}
