<?php

namespace Streeboga\Genesis\Utils;

class UsersUtils
{
    public static function avatarUrl(?string $email, int $size = 64, ?string $default = null): string
    {
        $hash = md5(strtolower(trim((string)$email)));
        $def = $default ?? 'identicon';
        return "https://www.gravatar.com/avatar/{$hash}?s={$size}&d={$def}";
    }

    public static function mergePreferences(array $existing, array $incoming): array
    {
        // deep merge with incoming taking precedence
        $result = $existing;
        foreach ($incoming as $key => $value) {
            if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
                $result[$key] = self::mergePreferences($result[$key], $value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    public static function linkToProject(int|string $projectId, int|string $userId): string
    {
        return "/projects/{$projectId}/users/{$userId}";
    }
}


