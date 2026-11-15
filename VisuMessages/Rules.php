<?php
declare(strict_types=1);

final class VisuMessagesRules
{
    public static function target(string $text, int $type): mixed
    {
        if ($type === 0) {
            return match (strtolower(trim($text))) {
                'true', '1', 'ja', 'an' => true,
                'false', '0', 'nein', 'aus' => false,
                default => throw new InvalidArgumentException('Boolescher Vergleichswert: true oder false verwenden.')
            };
        }
        if ($type === 1 || $type === 2) {
            $number = str_replace(',', '.', trim($text));
            if (!is_numeric($number) || !is_finite((float) $number)) {
                throw new InvalidArgumentException('Numerischer Vergleichswert erforderlich.');
            }
            return (float) $number;
        }
        return $text;
    }

    public static function matches(mixed $value, string $operator, mixed $target): bool
    {
        return match ($operator) {
            'eq' => is_bool($value) || is_string($value) ? $value === $target : (float) $value === (float) $target,
            'ne' => !self::matches($value, 'eq', $target),
            'lt' => $value < $target,
            'le' => $value <= $target,
            'gt' => $value > $target,
            'ge' => $value >= $target,
            'contains' => str_contains((string) $value, (string) $target),
            default => throw new InvalidArgumentException('Unbekannter Vergleichsoperator.')
        };
    }

    public static function trim(array $messages, int $limit): array
    {
        $normal = 0;
        return array_values(array_filter($messages, static function (array $item) use (&$normal, $limit): bool {
            return $item['pinned'] || ++$normal <= $limit;
        }));
    }

    public static function sorted(array $messages): array
    {
        usort($messages, static fn(array $a, array $b): int =>
            ((int) $b['pinned'] <=> (int) $a['pinned']) ?: ($b['id'] <=> $a['id']));
        return $messages;
    }
}
