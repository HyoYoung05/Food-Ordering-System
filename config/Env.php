<?php
declare(strict_types=1);

final class Env
{
    private static bool $loaded = false;

    public static function load(?string $path = null): void
    {
        if (self::$loaded) return;
        self::$loaded = true;
        $path ??= dirname(__DIR__).'/.env';
        if (!is_file($path) || !is_readable($path)) return;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (str_starts_with($line, 'export ')) $line = substr($line, 7);
            [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
            $name = trim($name);
            if (!preg_match('/^[A-Z_][A-Z0-9_]*$/i', $name) || getenv($name) !== false) continue;
            $value = trim($value);
            if (strlen($value) >= 2 && (($value[0] === '"' && str_ends_with($value, '"')) || ($value[0] === "'" && str_ends_with($value, "'")))) {
                $value = substr($value, 1, -1);
            } else {
                $comment = strpos($value, ' #');
                if ($comment !== false) $value = rtrim(substr($value, 0, $comment));
            }
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }

    public static function get(string $name, ?string $default = null): ?string
    {
        self::load();
        $value = getenv($name);
        return $value === false ? $default : $value;
    }
}
