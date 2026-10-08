<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function bin2hex;
use function class_exists;
use function dirname;
use function enum_exists;
use function file_put_contents;
use function is_array;

use Lemonfiber\Sdk\Generated\Contract;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Generated\RefusalDescription;
use Lemonfiber\Sdk\Generated\RefusalStatus;
use Lemonfiber\Sdk\Scripts\GeneratedSource;
use Lemonfiber\Sdk\Scripts\Refusals;
use Lemonfiber\Sdk\Scripts\ShapePlan;
use Lemonfiber\Sdk\Scripts\VendoredContract;
use LogicException;

use function random_bytes;
use function unlink;

/**
 * The generated refusal list, and what it says of each code, loaded with one
 * refusal beside those the vendored contract lists.
 *
 * Which codes the generated list names is whatever the vendored contract
 * lists, and that may be none. This writes the list the generator would write
 * from the vendored contract with one more refusal in it, and loads that in
 * place of the committed one, so a test has a code the list is sure to name
 * while every vendored code keeps its case and its status.
 */
final class GeneratedRefusals
{
    /**
     * Loads the list with the given refusal beside the vendored ones.
     *
     * @param  array{name: string, status: int, description: string}  $refusal
     *
     * @throws LogicException where the committed list was loaded first
     */
    public static function loadWith(string $code, array $refusal): void
    {
        if (enum_exists(RefusalCode::class, false) || class_exists(RefusalStatus::class, false) || class_exists(RefusalDescription::class, false)) {
            throw new LogicException('The committed refusal list was loaded before one with a refusal beside it could be.');
        }

        $root = dirname(__DIR__, 2);
        $artefact = new VendoredContract($root)->artefact();
        $vendored = $artefact['refusals'] ?? [];
        $artefact['refusals'] = (is_array($vendored) ? $vendored : []) + [$code => $refusal];

        $source = new GeneratedSource('the vendored contract and one refusal more', Contract::API_VERSION, new ShapePlan([], []));
        $listed = new Refusals()->listed($artefact);

        foreach ([$source->refusalEnum($listed), $source->refusalStatusClass($listed), $source->refusalDescriptionClass($listed)] as $written) {
            $path = $root . '/.refusal-code-' . bin2hex(random_bytes(6)) . '.php';

            file_put_contents($path, $written);

            require $path;

            unlink($path);
        }
    }
}
