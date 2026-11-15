<?php
declare(strict_types=1);
require_once __DIR__ . '/../VisuMessages/Rules.php';

function check(bool $result, string $label): void {
    if (!$result) {
        throw new RuntimeException('FEHLER: ' . $label);
    }
    echo 'OK: ' . $label . PHP_EOL;
}

check(VisuMessagesRules::target('false', 0) === false, 'false wird nicht zu true konvertiert');
check(VisuMessagesRules::target('4,5', 2) === 4.5, 'Dezimalkomma');
check(VisuMessagesRules::matches(4, 'lt', 5.0), 'Numerische Bedingung erfüllt');
check(!VisuMessagesRules::matches(5, 'lt', 5.0), 'Grenze ausgeschlossen');
check(VisuMessagesRules::matches(5, 'le', 5.0), 'Grenze eingeschlossen');
check(VisuMessagesRules::matches(false, 'eq', false), 'Boolean exakt');
check(!VisuMessagesRules::matches('01', 'eq', '1'), 'Text ohne numerische Konvertierung');
check(VisuMessagesRules::matches('Fenster offen', 'contains', 'offen'), 'Text enthält');
check(!VisuMessagesRules::matches('Fertig', 'eq', 'fertig'), 'Großschreibung');
$errors = 0;
foreach ([['vielleicht', 0], ['keine Zahl', 1], ['INF', 2]] as [$text, $type]) {
    try { VisuMessagesRules::target($text, $type); } catch (InvalidArgumentException) { $errors++; }
}
check($errors === 3, 'Ungültige Vergleichswerte abgewiesen');
$items = [
    ['id'=>4, 'pinned'=>false], ['id'=>3, 'pinned'=>true],
    ['id'=>2, 'pinned'=>false], ['id'=>1, 'pinned'=>true]
];
check(array_column(VisuMessagesRules::trim($items, 1), 'id') === [4,3,1], 'Limit erhält alle Pins und neueste normale Meldung');
check(array_column(VisuMessagesRules::sorted($items), 'id') === [3,1,4,2], 'Pins zuerst, je Gruppe neueste zuerst');
echo 'Alle Logiktests bestanden.' . PHP_EOL;
