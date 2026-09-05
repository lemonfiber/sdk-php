<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: v0.11.0, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every kind the contract describes, and the only ones this package reads.
 */
enum Kind: string
{
    case Admission = 'admission';
    case Archives = 'archives';
    case Backup = 'backup';
    case Bundle = 'bundle';
    case Clients = 'clients';
    case Config = 'config';
    case Dashboard = 'dashboard';
    case Doctor = 'doctor';
    case Error = 'error';
    case Forms = 'forms';
    case FrontDoor = 'front-door';
    case Glossary = 'glossary';
    case Household = 'household';
    case Invitation = 'invitation';
    case Job = 'job';
    case Lifecycle = 'lifecycle';
    case Log = 'log';
    case Music = 'music';
    case Outbound = 'outbound';
    case Preview = 'preview';
    case Pull = 'pull';
    case Quality = 'quality';
    case Removal = 'removal';
    case Repair = 'repair';
    case Reset = 'reset';
    case Restore = 'restore';
    case Seed = 'seed';
    case Setup = 'setup';
    case Start = 'start';
    case Status = 'status';
    case Step = 'step';
    case Stored = 'stored';
    case Stuck = 'stuck';
    case Trace = 'trace';
    case Undo = 'undo';
    case Upgrade = 'upgrade';
    case Version = 'version';
    case Walkthrough = 'walkthrough';
    case Watch = 'watch';
    case Wizard = 'wizard';
    case Word = 'word';
}
