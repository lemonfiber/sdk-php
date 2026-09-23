<?php

// Generated from contract/web-api.contract.json. Do not edit.
// Source: becca9b0c303f3ef3ef28b93fccca299db0d8d18, api_version 1.
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
    case Catalogue = 'catalogue';
    case Clients = 'clients';
    case Config = 'config';
    case Credentials = 'credentials';
    case Dashboard = 'dashboard';
    case Doctor = 'doctor';
    case Error = 'error';
    case Forms = 'forms';
    case FrontDoor = 'front-door';
    case Glossary = 'glossary';
    case Held = 'held';
    case History = 'history';
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
    case Plugins = 'plugins';
    case Preview = 'preview';
    case Provenance = 'provenance';
    case Pull = 'pull';
    case Quality = 'quality';
    case Removal = 'removal';
    case Repair = 'repair';
    case Replacement = 'replacement';
    case Reset = 'reset';
    case Restore = 'restore';
    case Seed = 'seed';
    case SelfUpdate = 'self-update';
    case Setup = 'setup';
    case Space = 'space';
    case Start = 'start';
    case Status = 'status';
    case Step = 'step';
    case StopSeeding = 'stop-seeding';
    case Stored = 'stored';
    case Stuck = 'stuck';
    case Substitution = 'substitution';
    case Trace = 'trace';
    case Undo = 'undo';
    case Uninstall = 'uninstall';
    case Update = 'update';
    case Upgrade = 'upgrade';
    case Version = 'version';
    case Walkthrough = 'walkthrough';
    case Watch = 'watch';
    case Wiring = 'wiring';
    case Wizard = 'wizard';
    case Word = 'word';
}
