<?php

declare(strict_types=1);

namespace Lemonfiber\Sdk\Tests\Support;

use function bin2hex;
use function dirname;
use function enum_exists;
use function file_get_contents;
use function file_put_contents;
use function is_array;
use function json_decode;

use Lemonfiber\Sdk\Generated\Contract;
use Lemonfiber\Sdk\Generated\RefusalCode;
use Lemonfiber\Sdk\Scripts\GeneratedSource;
use Lemonfiber\Sdk\Scripts\Refusals;
use LogicException;

use function random_bytes;
use function unlink;

/**
 * The generated refusal list, loaded with one refusal beside those the
 * vendored contract lists.
 *
 * Which codes the generated list names is whatever the vendored contract
 * lists, and that may be none. This writes the list the generator would write
 * from the vendored contract with one more refusal in it, and loads that in
 * place of the committed one, so a test has a code the list is sure to name
 * while every vendored code keeps its case and its status.
 */
final class GeneratedRefusals
{
    private const string ARTEFACT = 'contract/web-api.contract.json';

    /**
     * Loads the list with the given refusal beside the vendored ones.
     *
     * @param  array{name: string, status: int, description: string}  $refusal
     *
     * @throws LogicException where the committed list was loaded first
     */
    public static function loadWith(string $code, array $refusal): void
    {
        if (enum_exists(RefusalCode::class, false)) {
            throw new LogicException('The committed refusal list was loaded before one with a refusal beside it could be.');
        }

        $root = dirname(__DIR__, 2);
        $artefact = json_decode((string) file_get_contents($root . '/' . self::ARTEFACT), true, 64, JSON_THROW_ON_ERROR);
        $artefact = is_array($artefact) ? $artefact : [];
        $vendored = $artefact['refusals'] ?? [];
        $artefact['refusals'] = (is_array($vendored) ? $vendored : []) + [$code => $refusal];

        $source = new GeneratedSource(self::ARTEFACT, 'the vendored contract and one refusal more', Contract::API_VERSION)
            ->refusalEnum(new Refusals()->listed($artefact));
        $path = $root . '/.refusal-code-' . bin2hex(random_bytes(6)) . '.php';

        file_put_contents($path, $source);

        require $path;

        unlink($path);
    }
}
