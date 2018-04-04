<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent;

use JsonSerializable;
use function array_filter;
use function array_map;
use function is_array;

final class Serialization
{
    /**
     * @param mixed[] $input
     * @return mixed[]
     */
    public static function filterUnset(array $input): array
    {
        return array_filter($input, function ($value): bool {
            if ($value === null) {
                return false;
            }

            if (is_array($value) && empty($value)) {
                return false;
            }

            return true;
        });
    }

    /**
     * @return mixed[]
     */
    public static function serialize(JsonSerializable ...$serializable): array
    {
        return array_map(function (JsonSerializable $frame) {
            return $frame->jsonSerialize();
        }, $serializable);
    }
}
