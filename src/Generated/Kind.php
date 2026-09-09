<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: e6cd57b374cee20ba03feb8f18df4b1743056976, api_version 1.
// Regenerate with `composer contract:generate`.

declare(strict_types=1);

namespace Lemonfiber\Sdk\Generated;

/**
 * Every kind the contract describes, and the only ones this package reads.
 */
enum Kind: string
{
    case Admission = 'admission';
    case Adoption = 'adoption';
    case Alerts = 'alerts';
    case Archives = 'archives';
    case Backup = 'backup';
    case Bandwidth = 'bandwidth';
    case Beside = 'beside';
    case Bundle = 'bundle';
    case Clients = 'clients';
    case Config = 'config';
    case Credentials = 'credentials';
    case Dashboard = 'dashboard';
    case Doctor = 'doctor';
    case Error = 'error';
    case Forms = 'forms';
    case FrontDoor = 'front-door';
    case Glossary = 'glossary';
    case Hosting = 'hosting';
    case Household = 'household';
    case Import = 'import';
    case Invitation = 'invitation';
    case Job = 'job';
    case Lifecycle = 'lifecycle';
    case Log = 'log';
    case Migration = 'migration';
    case Music = 'music';
    case Outbound = 'outbound';
    case Preview = 'preview';
    case Pull = 'pull';
    case Quality = 'quality';
    case Removal = 'removal';
    case Repair = 'repair';
    case Replacement = 'replacement';
    case Reset = 'reset';
    case Restore = 'restore';
    case Seed = 'seed';
    case Setup = 'setup';
    case Space = 'space';
    case Start = 'start';
    case Status = 'status';
    case Step = 'step';
    case StopSeeding = 'stop-seeding';
    case Stored = 'stored';
    case Stuck = 'stuck';
    case Trace = 'trace';
    case Undo = 'undo';
    case Uninstall = 'uninstall';
    case Upgrade = 'upgrade';
    case Version = 'version';
    case Walkthrough = 'walkthrough';
    case Watch = 'watch';
    case Wizard = 'wizard';
    case Word = 'word';
}
