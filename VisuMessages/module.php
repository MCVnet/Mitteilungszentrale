<?php
declare(strict_types=1);
require_once __DIR__ . '/Rules.php';

class VisuMessages extends IPSModuleStrict
{
    private const TILE_MODULE = '{B5B875BB-9B76-45FD-4E67-2607E45B3AC4}';
    private const WEBFRONT_MODULE = '{3565B1F2-8F7B-4311-A4B6-1BF1D868F39E}';
    private const ANNOUNCEMENT_MODULE = '{26F752F0-4D4E-A52C-DB3C-35DFEA979F44}';
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyString('Rules', '[]');
        $this->RegisterPropertyInteger('Limit', 200);
        $this->RegisterPropertyInteger('Height', 480);
        $this->RegisterPropertyString('BaseURL', '');
        $this->RegisterPropertyBoolean('ShowModuleData', false);
        $this->RegisterPropertyBoolean('DebugEnabled', false);
        $this->RegisterPropertyString('HeadingTitle', 'Meldungen');
        $this->RegisterPropertyBoolean('ShowHeading', true);
        $this->RegisterPropertyBoolean('UseVariableIcons', true);
        $this->RegisterPropertyInteger('IconSize', 48);
        $this->RegisterPropertyBoolean('EmbedSDKIcons', true);
        $this->RegisterPropertyInteger('LocalWebPort', 3777);
        $this->RegisterPropertyBoolean('CustomColors', false);
        $this->RegisterPropertyInteger('BackgroundColor', 0x141c25);
        $this->RegisterPropertyInteger('MessageColor', 0x1e2a36);
        $this->RegisterPropertyInteger('TextColor', 0xedf3f7);
        $this->RegisterPropertyInteger('AccentColor', 0x7ed8b6);
        foreach (['WebFront', 'IPSView', 'Tile'] as $view) {
            $this->RegisterPropertyBoolean($view . 'OwnColors', false);
            foreach (['BackgroundColor' => 0x141c25, 'MessageColor' => 0x1e2a36, 'TextColor' => 0xedf3f7, 'AccentColor' => 0x7ed8b6] as $property => $default) {
                $this->RegisterPropertyInteger($view . $property, $default);
            }
            $this->RegisterPropertyString($view . 'CSS', '');
        }
        $this->RegisterPropertyString('SDKColorProfile', 'auto');
        $this->RegisterPropertyString('MessageSize', 'normal');
        $this->RegisterPropertyInteger('MessageMinWidth', 240);
        $this->RegisterPropertyInteger('MessageMaxWidth', 760);
        $this->RegisterPropertyInteger('MessageMinHeight', 60);
        $this->RegisterPropertyBoolean('MessageScaleContents', true);
        $this->RegisterPropertyInteger('MessageFontSize', 15);
        $this->RegisterPropertyInteger('MessageMetaFontSize', 11);
        $this->RegisterPropertyInteger('MessageBorderColor', 0x374652);
        $this->RegisterPropertyInteger('MessageBorderWidth', 1);
        $this->RegisterPropertyInteger('MessageRadius', 12);
        $this->RegisterPropertyString('MessageShadow', 'none');
        $this->RegisterPropertyString('AppearanceEditor', 'tile');
        foreach (['Tile', 'WebFront', 'IPSView'] as $view) {
            $this->RegisterPropertyBoolean($view . 'OwnLayout', false);
            foreach (['UseVariableIcons', 'MessageScaleContents', 'ShowHeading'] as $property) {
                $this->RegisterPropertyBoolean($view . $property, $this->ReadPropertyBoolean($property));
            }
            foreach (['MessageSize', 'MessageShadow', 'HeadingTitle'] as $property) {
                $this->RegisterPropertyString($view . $property, $this->ReadPropertyString($property));
            }
            foreach (['MessageMinWidth', 'MessageMaxWidth', 'MessageMinHeight', 'IconSize', 'MessageFontSize', 'MessageMetaFontSize', 'MessageBorderColor', 'MessageBorderWidth', 'MessageRadius'] as $property) {
                $this->RegisterPropertyInteger($view . $property, $this->ReadPropertyInteger($property));
            }
        }
        $this->RegisterAttributeString('Store', '{"next":1,"messages":[]}');
        $this->RegisterAttributeString('Token', '');
        $this->RegisterAttributeInteger('UpdatedAt', 0);
        $this->RegisterAttributeString('AnnouncementQueue', '[]');
        $this->RegisterAttributeInteger('AnnouncementGeneration', 0);
        $this->RegisterTimer('Announcements', 0, 'VMMSG_ProcessAnnouncements($_IPS["TARGET"]);');
        $this->RegisterVariableString('HTML', 'Meldungen (WebFront / IPSView)', [
            'PRESENTATION' => VARIABLE_PRESENTATION_LEGACY, 'PROFILE' => '~HTMLBox'
        ], 0);
        $this->RegisterVariableString('URL', 'WebView-Adresse (mit Zugriffsschlüssel)', [
            'PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION
        ], 10);
        $this->registerDataVariables();
        // HTML-SDK: documented value 1; symbolic constant is unavailable in some 9.0 builds.
        $this->SetVisualizationType(1);
        $this->RegisterHook('visu-messages-' . $this->InstanceID);
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);
    }

    public function ApplyChanges(): void
    {
        parent::ApplyChanges();
        if (in_array(IPS_GetName($this->InstanceID), ['Visu-Messages', 'VisuMessages'], true)) {
            IPS_SetName($this->InstanceID, 'Mitteilungszentrale');
        }
        $this->registerDataVariables();
        // Replace previously cleared colors with the original solid defaults once.
        $normalized = false;
        $rules = json_decode($this->ReadPropertyString('Rules'), true);
        if (is_array($rules) && array_is_list($rules)) {
            $changed = false;
            foreach ($rules as &$rule) {
                if (!is_array($rule)) {
                    continue;
                }
                if (($rule['PushTitle'] ?? '') === 'Visu-Messages') {
                    $rule['PushTitle'] = 'Mitteilungszentrale';
                    $changed = true;
                }
                foreach (['Announce' => false, 'AnnouncementID' => 0, 'AnnouncementRepeats' => 0, 'AnnouncementInterval' => 60, 'AnnouncementOwnVolume' => false, 'AnnouncementVolume' => 50, 'PushEnabled' => false, 'PushVisuID' => 0, 'PushTitle' => 'Mitteilungszentrale', 'PushTargetID' => 0, 'PushRepeats' => 0, 'PushInterval' => 60, 'Priority' => 3] as $field => $default) {
                    if (!array_key_exists($field, $rule)) {
                        $rule[$field] = $default;
                        $changed = true;
                    }
                }
                if (is_array($rule) && ($rule['Text'] ?? '') === '{name}: {value}') {
                    $rule['Text'] = '{parent} · {name}: {value}';
                    $changed = true;
                }
            }
            unset($rule);
            if ($changed) {
                IPS_SetProperty($this->InstanceID, 'Rules', json_encode($rules, JSON_THROW_ON_ERROR));
                $normalized = true;
            }
        }
        foreach (['', 'WebFront', 'IPSView', 'Tile'] as $prefix) {
            foreach (['BackgroundColor' => 0x141c25, 'MessageColor' => 0x1e2a36, 'TextColor' => 0xedf3f7, 'AccentColor' => 0x7ed8b6] as $property => $default) {
                if ($this->ReadPropertyInteger($prefix . $property) < 0) {
                    IPS_SetProperty($this->InstanceID, $prefix . $property, $default);
                    $normalized = true;
                }
            }
        }
        if ($normalized) {
            IPS_ApplyChanges($this->InstanceID);
            return;
        }
        // Remove all oversized icon buffers left by versions 0.10 and 0.11.
        foreach ($this->GetBufferList() as $buffer) {
            if (str_starts_with($buffer, 'IconLibrary')) {
                $this->SetBuffer($buffer, '');
            }
        }
        if ($this->ReadAttributeString('Token') === '') {
            $this->WriteAttributeString('Token', bin2hex(random_bytes(32)));
        }
        if (IPS_GetKernelRunlevel() !== KR_READY) {
            return;
        }
        $this->configure();
    }

    private function configure(): void
    {
        $this->locked(function (): void {
            $this->WriteAttributeInteger('AnnouncementGeneration', $this->ReadAttributeInteger('AnnouncementGeneration') + 1);
            $this->writeAnnouncementQueue([]);
            foreach ($this->GetReferenceList() as $id) {
                $this->UnregisterMessage($id, VM_UPDATE);
                $this->UnregisterMessage($id, VM_DELETE);
                $this->UnregisterReference($id);
            }
            try {
                $rules = $this->validatedRules();
                if ($this->ReadPropertyInteger('Limit') < 1 || $this->ReadPropertyInteger('Limit') > 5000) {
                    throw new InvalidArgumentException('Speicherlimit muss zwischen 1 und 5000 liegen.');
                }
                $base = rtrim($this->ReadPropertyString('BaseURL'), '/');
                if ($base !== '' && (!filter_var($base, FILTER_VALIDATE_URL) || !in_array(parse_url($base, PHP_URL_SCHEME), ['http', 'https'], true) || parse_url($base, PHP_URL_QUERY) !== null || parse_url($base, PHP_URL_FRAGMENT) !== null)) {
                    throw new InvalidArgumentException('Basisadresse muss eine HTTP(S)-Adresse ohne Query oder Fragment sein.');
                }
                foreach ($rules as $rule) {
                    $id = $rule['VariableID'];
                    $this->RegisterReference($id);
                    $this->RegisterMessage($id, VM_UPDATE);
                    $this->RegisterMessage($id, VM_DELETE);
                    if ($rule['Announce'] && $rule['AnnouncementAvailable']) {
                        $this->RegisterReference($rule['AnnouncementID']);
                    }
                    if ($rule['PushEnabled'] && $rule['PushAvailable']) {
                        $this->RegisterReference($rule['PushVisuID']);
                        if ($rule['PushTargetID'] > 0) {
                            $this->RegisterReference($rule['PushTargetID']);
                        }
                    }
                }
                $this->SetBuffer('ActiveRules', json_encode($rules, JSON_THROW_ON_ERROR));
                $this->SetBuffer('Error', '');
                $this->SetStatus(102);
            } catch (Throwable $e) {
                $this->SetBuffer('ActiveRules', '[]');
                $this->SetBuffer('Error', $e->getMessage());
                $this->SetStatus(200);
            }
            $this->updateLinks();
            $store = $this->store();
            $store['messages'] = VisuMessagesRules::trim($store['messages'], max(1, $this->ReadPropertyInteger('Limit')));
            $this->save($store);
        });
    }

    private function validatedRules(): array
    {
        $rules = json_decode($this->ReadPropertyString('Rules'), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($rules) || !array_is_list($rules)) {
            throw new InvalidArgumentException('Regeln müssen eine Liste sein.');
        }
        $active = [];
        $availability = [];
        $announcementInstalled = in_array(self::ANNOUNCEMENT_MODULE, IPS_GetModuleList(), true);
        $this->SetBuffer('AvailabilityInfo', '[]');
        foreach ($rules as $index => $rule) {
            if (!($rule['Enabled'] ?? true)) {
                continue;
            }
            $id = (int) ($rule['VariableID'] ?? 0);
            if (!IPS_VariableExists($id) || IPS_GetParent($id) === $this->InstanceID) {
                throw new InvalidArgumentException('Regel ' . ($index + 1) . ': gültige externe Variable auswählen.');
            }
            $type = IPS_GetVariable($id)['VariableType'];
            $op = $rule['Operator'] ?? 'eq';
            $allowed = match ($type) {
                0 => ['eq', 'ne'],
                1, 2 => ['eq', 'ne', 'lt', 'le', 'gt', 'ge'],
                3 => ['eq', 'ne', 'contains']
            };
            if (!in_array($op, $allowed, true) || trim($rule['Text'] ?? '') === '') {
                throw new InvalidArgumentException('Regel ' . ($index + 1) . ': Operator oder Nachrichtentext ungültig.');
            }
            $rule['Priority'] = (int) ($rule['Priority'] ?? 3);
            if (!in_array($rule['Priority'], [1, 2, 3], true)) {
                throw new InvalidArgumentException('Regel ' . ($index + 1) . ': Priorität muss 1, 2 oder 3 sein.');
            }
            $rule['VariableID'] = $id;
            $rule['Operator'] = $op;
            $rule['Target'] = VisuMessagesRules::target((string) ($rule['Value'] ?? ''), $type);
            $rule['Announce'] = (bool) ($rule['Announce'] ?? false);
            $rule['AnnouncementID'] = (int) ($rule['AnnouncementID'] ?? 0);
            $rule['AnnouncementRepeats'] = (int) ($rule['AnnouncementRepeats'] ?? 0);
            $rule['AnnouncementInterval'] = (int) ($rule['AnnouncementInterval'] ?? 60);
            $rule['AnnouncementOwnVolume'] = (bool) ($rule['AnnouncementOwnVolume'] ?? false);
            $rule['AnnouncementVolume'] = (int) ($rule['AnnouncementVolume'] ?? 50);
            $rule['AnnouncementAvailable'] = true;
            if ($rule['Announce']) {
                if ($rule['AnnouncementOwnVolume'] && ($rule['AnnouncementVolume'] < 1 || $rule['AnnouncementVolume'] > 100)) {
                    throw new InvalidArgumentException('Regel ' . ($index + 1) . ': Durchsage-Lautstärke muss zwischen 1 und 100 Prozent liegen.');
                }
                $device = $rule['AnnouncementID'];
                if (!$announcementInstalled || !IPS_InstanceExists($device) || IPS_GetInstance($device)['ModuleInfo']['ModuleID'] !== self::ANNOUNCEMENT_MODULE) {
                    $rule['AnnouncementAvailable'] = false;
                    $availability[] = 'Regel ' . ($index + 1) . ': Durchsage-Instanz fehlt oder ist ungeeignet. Die Meldungsliste funktioniert weiterhin.';
                }
                if ($rule['AnnouncementRepeats'] < 0 || $rule['AnnouncementRepeats'] > 100 ||
                    $rule['AnnouncementInterval'] < 1 || $rule['AnnouncementInterval'] > 86400) {
                    throw new InvalidArgumentException('Regel ' . ($index + 1) . ': Wiederholungen 0–100, Abstand 1–86400 Sekunden.');
                }
            }
            $rule['PushEnabled'] = (bool) ($rule['PushEnabled'] ?? false);
            $rule['PushVisuID'] = (int) ($rule['PushVisuID'] ?? 0);
            $rule['PushTitle'] = (string) ($rule['PushTitle'] ?? 'Mitteilungszentrale');
            $rule['PushTargetID'] = (int) ($rule['PushTargetID'] ?? 0);
            $rule['PushRepeats'] = (int) ($rule['PushRepeats'] ?? 0);
            $rule['PushInterval'] = (int) ($rule['PushInterval'] ?? 60);
            $rule['PushAvailable'] = true;
            if ($rule['PushEnabled']) {
                if ($rule['PushRepeats'] < 0 || $rule['PushRepeats'] > 100 || $rule['PushInterval'] < 1 || $rule['PushInterval'] > 86400) {
                    throw new InvalidArgumentException('Regel ' . ($index + 1) . ': Push-Wiederholungen 0–100, Abstand 1–86400 Sekunden.');
                }
                if (!IPS_InstanceExists($rule['PushVisuID']) || !in_array(IPS_GetInstance($rule['PushVisuID'])['ModuleInfo']['ModuleID'], [self::TILE_MODULE, self::WEBFRONT_MODULE], true)) {
                    $rule['PushAvailable'] = false;
                    $availability[] = 'Regel ' . ($index + 1) . ': Push-Visualisierung fehlt oder ist ungeeignet. Die Meldungsliste funktioniert weiterhin.';
                }
                if ($rule['PushTargetID'] < 0 || ($rule['PushTargetID'] > 0 && !IPS_ObjectExists($rule['PushTargetID']))) {
                    $rule['PushAvailable'] = false;
                    $availability[] = 'Regel ' . ($index + 1) . ': Push-Sprungziel fehlt. Push bleibt für diese Regel aus; die Meldungsliste funktioniert weiterhin.';
                }
            }
            $rule['AnnouncementKey'] = hash('sha256', json_encode($rule, JSON_THROW_ON_ERROR));
            $active[] = $rule;
        }
        $this->SetBuffer('AvailabilityInfo', json_encode($availability, JSON_THROW_ON_ERROR));
        return $active;
    }

    public function MessageSink(int $TimeStamp, int $SenderID, int $Message, array $Data): void
    {
        if ($Message === IPS_KERNELMESSAGE && ($Data[0] ?? null) === KR_READY) {
            $this->configure();
            return;
        }
        if ($Message === VM_DELETE) {
            $this->configure();
            return;
        }
        if ($Message !== VM_UPDATE || !($Data[1] ?? false) || !array_key_exists(2, $Data)) {
            return;
        }
        $this->locked(function () use ($SenderID, $Data): void {
            $store = $this->store();
            $changed = false;
            foreach (json_decode($this->GetBuffer('ActiveRules') ?: '[]', true) as $rule) {
                if ($rule['VariableID'] !== $SenderID) {
                    continue;
                }
                if (!VisuMessagesRules::matches($Data[0], $rule['Operator'], $rule['Target']) ||
                    VisuMessagesRules::matches($Data[2], $rule['Operator'], $rule['Target'])) {
                    continue;
                }
                $format = static fn(mixed $value): string => is_bool($value) ? ($value ? 'true' : 'false') : (string) $value;
                $parentID = IPS_GetParent($SenderID);
                $parentName = $parentID > 0 ? IPS_GetName($parentID) : 'IP-Symcon';
                $text = strtr($rule['Text'], [
                    '{parent}' => $parentName,
                    '{name}' => IPS_GetName($SenderID), '{value}' => $format($Data[0]),
                    '{old}' => $format($Data[2]), '{id}' => (string) $SenderID
                ]);
                $messageID = $store['next']++;
                $this->debug('Meldung erstellt', ['id' => $messageID, 'variable' => $SenderID, 'durchsage' => $rule['Announce'] && $rule['AnnouncementAvailable'], 'push' => $rule['PushEnabled'] && $rule['PushAvailable']]);
                array_unshift($store['messages'], [
                    'id' => $messageID, 'time' => time(), 'text' => $text, 'priority' => $rule['Priority'],
                    'source' => IPS_GetName($SenderID), 'variableID' => $SenderID, 'pinned' => false,
                    'announcement' => ['enabled' => $rule['Announce'], 'repeats' => $rule['AnnouncementRepeats'],
                        'interval' => $rule['AnnouncementInterval'],
                        'volume' => $rule['AnnouncementOwnVolume'] ? $rule['AnnouncementVolume'] : null],
                    'push' => ['enabled' => $rule['PushEnabled'], 'visuID' => $rule['PushVisuID'],
                        'repeats' => $rule['PushRepeats'], 'interval' => $rule['PushInterval']]
                ]);
                if ($rule['PushEnabled'] && $rule['PushAvailable']) {
                    $queue = json_decode($this->ReadAttributeString('AnnouncementQueue'), true, 512, JSON_THROW_ON_ERROR);
                    $queue[] = ['kind' => 'push', 'messageID' => $messageID, 'device' => $rule['PushVisuID'], 'text' => $text,
                        'title' => $rule['PushTitle'], 'target' => $rule['PushTargetID'] ?: $this->InstanceID,
                        'remaining' => 1 + $rule['PushRepeats'], 'interval' => $rule['PushInterval'], 'due' => time(), 'ruleKey' => $rule['AnnouncementKey'],
                        'generation' => $this->ReadAttributeInteger('AnnouncementGeneration')];
                    $this->writeAnnouncementQueue($queue);
                }
                if ($rule['Announce'] && $rule['AnnouncementAvailable']) {
                    $queue = json_decode($this->ReadAttributeString('AnnouncementQueue'), true, 512, JSON_THROW_ON_ERROR);
                    $queue[] = ['messageID' => $messageID, 'device' => $rule['AnnouncementID'], 'text' => $text,
                        'remaining' => 1 + $rule['AnnouncementRepeats'], 'interval' => $rule['AnnouncementInterval'],
                        'volume' => $rule['AnnouncementOwnVolume'] ? $rule['AnnouncementVolume'] : null,
                        'due' => time(), 'ruleKey' => $rule['AnnouncementKey'],
                        'generation' => $this->ReadAttributeInteger('AnnouncementGeneration')];
                    $this->writeAnnouncementQueue($queue);
                }
                $changed = true;
            }
            if ($changed) {
                $store['messages'] = VisuMessagesRules::trim($store['messages'], $this->ReadPropertyInteger('Limit'));
                $this->save($store);
            }
        });
    }

    /** Timer worker: playback runs outside the message-store lock. */
    public function ProcessAnnouncements(): void
    {
        $worker = 'Visu-Messages.Announcements.' . $this->InstanceID;
        if (!IPS_SemaphoreEnter($worker, 1)) {
            return;
        }
        try {
            $job = null;
            $this->locked(function () use (&$job): void {
                $queue = $this->prunedAnnouncementQueue();
                usort($queue, static fn(array $a, array $b): int => $a['due'] <=> $b['due']);
                if ($queue !== [] && $queue[0]['due'] <= time()) {
                    $job = array_shift($queue);
                }
                // Claim before calling the device: no duplicate playback on overlapping timer callbacks.
                $this->writeAnnouncementQueue($queue);
            });
            if ($job === null) {
                return;
            }
            $this->debug('Ausgabe gestartet', ['meldung' => $job['messageID'], 'typ' => $job['kind'] ?? 'audio', 'ziel' => $job['device'], 'verbleibend' => $job['remaining']]);
            try {
                if (($job['kind'] ?? 'audio') === 'push') {
                    $this->sendPush($job);
                } else {
                    if (!function_exists('DS_Play') || !IPS_InstanceExists($job['device']) ||
                        IPS_GetInstance($job['device'])['ModuleInfo']['ModuleID'] !== self::ANNOUNCEMENT_MODULE) {
                        throw new RuntimeException('Durchsage-Modul oder Ausgabegerät nicht verfügbar.');
                    }
                    if (IPS_GetInstance($job['device'])['InstanceStatus'] !== 102) {
                        throw new RuntimeException('Durchsage-Instanz ist nicht betriebsbereit.');
                    }
                    // The current official module returns void on success; older releases may return bool.
                    if ($this->playAnnouncement($job) === false) {
                        throw new RuntimeException('Durchsage wurde vom Modul abgelehnt.');
                    }
                }
                $this->locked(function () use ($job): void {
                    $queue = $this->prunedAnnouncementQueue();
                    if ($job['remaining'] > 1 && $this->announcementAllowed($job)) {
                        $job['remaining']--;
                        $job['due'] = time() + $job['interval'];
                        $queue[] = $job;
                    }
                    $this->writeAnnouncementQueue($queue);
                });
                $this->debug('Ausgabe abgeschlossen', ['meldung' => $job['messageID'], 'typ' => $job['kind'] ?? 'audio']);
            } catch (Throwable $e) {
                $type = ($job['kind'] ?? 'audio') === 'push' ? 'Push' : 'Durchsage';
                $this->debug($type, $e->getMessage(), 0);
                $this->LogMessage($type . ' für Meldung ' . $job['messageID'] . ' abgebrochen: ' . $e->getMessage(), KL_WARNING);
            }
        } finally {
            IPS_SemaphoreLeave($worker);
        }
    }

    private function sendPush(array $job): void
    {
        if (!IPS_InstanceExists($job['device'])) {
            throw new RuntimeException('Ziel-Visualisierung nicht verfügbar.');
        }
        $module = IPS_GetInstance($job['device'])['ModuleInfo']['ModuleID'];
        $title = $this->pushText($job['title'], 32);
        $text = $this->pushText($job['text'], 256);
        if ($module === self::TILE_MODULE && function_exists('VISU_PostNotification')) {
            $result = VISU_PostNotification($job['device'], $title, $text, 'alarm', $job['target']);
        } elseif ($module === self::WEBFRONT_MODULE && function_exists('WFC_PushNotification')) {
            $result = WFC_PushNotification($job['device'], $title, $text, 'alarm', $job['target']);
        } else {
            throw new RuntimeException('Push-Funktion für diese Visualisierung nicht verfügbar.');
        }
        if ($result === false) {
            throw new RuntimeException('Push-Nachricht wurde nicht angenommen. Zielobjekt und Gerätezuordnung prüfen.');
        }
    }

    private function pushText(string $text, int $limit): string
    {
        preg_match_all('/./us', $text, $characters);
        return implode('', array_slice($characters[0], 0, $limit));
    }

    private function playAnnouncement(array $job): mixed
    {
        $device = $job['device'];
        $lock = 'Visu-Messages.Durchsage.' . $device;
        if (!IPS_SemaphoreEnter($lock, 1000)) {
            throw new RuntimeException('Durchsage-Gerät wird bereits verwendet.');
        }
        $changed = false;
        $property = '';
        $original = null;
        $requested = null;
        try {
            if (($job['volume'] ?? null) !== null) {
                if (IPS_HasChanges($device)) {
                    throw new RuntimeException('Durchsage-Instanz hat ungespeicherte Änderungen; Lautstärke wurde nicht geändert.');
                }
                $output = IPS_GetProperty($device, 'OutputType');
                if (!in_array($output, [0, 1], true)) {
                    throw new RuntimeException('Ausgabetyp der Durchsage-Instanz wird nicht unterstützt.');
                }
                $property = $output === 0 ? 'SonosVolume' : 'MediaPlayerVolume';
                $requested = $output === 0 ? (string) $job['volume'] : (int) $job['volume'];
                $original = IPS_GetProperty($device, $property);
                if ($original !== $requested) {
                    // DS_Play has no volume parameter. Override its setting only for this call.
                    IPS_SetProperty($device, $property, $requested);
                    $changed = true;
                    IPS_ApplyChanges($device);
                }
            }
            return DS_Play($device, $job['text']);
        } finally {
            try {
                if ($changed && IPS_InstanceExists($device) && IPS_GetProperty($device, $property) === $requested) {
                    IPS_SetProperty($device, $property, $original);
                    IPS_ApplyChanges($device);
                }
            } finally {
                IPS_SemaphoreLeave($lock);
            }
        }
    }

    private function announcementAllowed(array $job): bool
    {
        if ($job['generation'] !== $this->ReadAttributeInteger('AnnouncementGeneration')) {
            return false;
        }
        $ids = array_column($this->store()['messages'], 'id');
        if (!in_array($job['messageID'], $ids, true)) {
            return false;
        }
        foreach (json_decode($this->GetBuffer('ActiveRules') ?: '[]', true) as $rule) {
            if ((($rule['Announce'] ?? false) || ($rule['PushEnabled'] ?? false)) && $rule['AnnouncementKey'] === $job['ruleKey']) {
                return true;
            }
        }
        return false;
    }

    private function prunedAnnouncementQueue(): array
    {
        $queue = json_decode($this->ReadAttributeString('AnnouncementQueue'), true, 512, JSON_THROW_ON_ERROR);
        $ids = array_fill_keys(array_column($this->store()['messages'], 'id'), true);
        $rules = json_decode($this->GetBuffer('ActiveRules') ?: '[]', true);
        $keys = array_fill_keys(array_column(array_filter($rules, static fn(array $rule): bool => (bool) (($rule['Announce'] ?? false) || ($rule['PushEnabled'] ?? false))), 'AnnouncementKey'), true);
        $generation = $this->ReadAttributeInteger('AnnouncementGeneration');
        return array_values(array_filter($queue, static fn(array $job): bool =>
            $job['generation'] === $generation && isset($ids[$job['messageID']], $keys[$job['ruleKey']])));
    }

    private function writeAnnouncementQueue(array $queue): void
    {
        $this->WriteAttributeString('AnnouncementQueue', json_encode(array_values($queue), JSON_THROW_ON_ERROR));
        $delay = $queue === [] ? 0 : max(1000, min(86400000, (min(array_column($queue, 'due')) - time()) * 1000));
        $this->SetTimerInterval('Announcements', $delay);
    }

    public function GetVisualizationTile(): string
    {
        return $this->page(['mode' => 'sdk', 'messages' => $this->messagesForVisualization($this->store())]);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        $this->debug('Visualization action', json_encode(['action' => $Ident, 'valueType' => get_debug_type($Value), 'id' => is_int($Value) ? $Value : null]), 0);
        try {
            if ($Ident === 'Refresh') {
                $this->locked(fn() => $this->publish($this->store()));
                return;
            }
            if ($Ident === 'ClearUnpinned') {
                if ($Value !== 1) {
                    throw new InvalidArgumentException('Sammellöschen wurde nicht bestätigt.');
                }
                $Ident = 'Clear';
            }
            $this->change($Ident, $Value);
        } catch (Throwable $e) {
            $this->debug('Visualization error', $e->getMessage(), 0);
            $this->UpdateVisualizationValue(json_encode(['error' => $e->getMessage()], JSON_THROW_ON_ERROR));
        }
    }

    private function change(string $action, mixed $value): void
    {
        if (!in_array($action, ['Pin', 'Unpin', 'Delete', 'Clear'], true)) {
            throw new InvalidArgumentException('Unbekannte Aktion.');
        }
        if ($action !== 'Clear' && (!is_int($value) || $value < 1)) {
            throw new InvalidArgumentException('Ungültige Meldungs-ID.');
        }
        $this->locked(function () use ($action, $value): void {
            $store = $this->store();
            foreach ($store['messages'] as $key => &$item) {
                if ($action === 'Clear') {
                    if (!$item['pinned']) {
                        unset($store['messages'][$key]);
                    }
                } elseif ($item['id'] === $value) {
                    if ($action === 'Delete') {
                        // Angepinnte Einträge müssen zuerst gelöst werden.
                        if ($item['pinned']) {
                            throw new InvalidArgumentException('Meldung vor dem Löschen lösen.');
                        }
                        unset($store['messages'][$key]);
                    } else {
                        $item['pinned'] = $action === 'Pin';
                    }
                }
            }
            unset($item);
            $store['messages'] = VisuMessagesRules::trim(array_values($store['messages']), $this->ReadPropertyInteger('Limit'));
            $this->save($store);
        });
    }

    public function AddTestMessage(): void
    {
        $this->locked(function (): void {
            $store = $this->store();
            array_unshift($store['messages'], [
                'id' => $store['next']++, 'time' => time(), 'text' => 'Die Mitteilungszentrale ist bereit.',
                'source' => 'Testmeldung', 'variableID' => 0, 'pinned' => false
            ]);
            $store['messages'] = VisuMessagesRules::trim($store['messages'], max(1, $this->ReadPropertyInteger('Limit')));
            $this->save($store);
        });
    }

    public function RotateAccessKey(): void
    {
        $this->locked(function (): void {
            $this->WriteAttributeString('Token', bin2hex(random_bytes(32)));
            $this->updateLinks();
        });
    }

    protected function ProcessHookData(): void
    {
        header('Cache-Control: no-store');
        header('Referrer-Policy: no-referrer');
        header('X-Content-Type-Options: nosniff');
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $api = isset($_GET['api']);
        $token = $api ? ($_SERVER['HTTP_X_MZ_TOKEN'] ?? '') : ($_GET['key'] ?? '');
        $expected = $this->ReadAttributeString('Token');
        if (!is_string($token) || $expected === '' || !hash_equals($expected, $token)) {
            http_response_code(403);
            echo 'Zugriff verweigert.';
            return;
        }
        if (!$api && $method === 'GET') {
            header('Content-Type: text/html; charset=utf-8');
            header("Content-Security-Policy: default-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; font-src 'self' data:; img-src 'self' data:; connect-src 'self'; base-uri 'none'; form-action 'none'");
            echo $this->page(['mode' => 'web', 'token' => $token, 'messages' => []]);
            return;
        }
        header('Content-Type: application/json; charset=utf-8');
        try {
            if (!$api || !in_array($method, ['GET', 'POST'], true)) {
                http_response_code(405);
                echo '{"error":"Methode nicht erlaubt"}';
                return;
            }
            if ($method === 'POST') {
                $body = file_get_contents('php://input', false, null, 0, 2049);
                if (strlen($body) > 2048) {
                    throw new InvalidArgumentException('Anfrage zu groß.');
                }
                $request = json_decode($body, true, 16, JSON_THROW_ON_ERROR);
                $this->change((string) ($request['action'] ?? ''), $request['id'] ?? 0);
            }
            echo json_encode(['messages' => $this->messagesForVisualization($this->store()), 'appearance' => $this->appearance(), 'updatedAt' => $this->ReadAttributeInteger('UpdatedAt')], JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function page(array $config): string
    {
        $config['instanceID'] = $this->InstanceID;
        $config['appearance'] = $this->appearance();
        $config['updatedAt'] = $this->ReadAttributeInteger('UpdatedAt');
        $assetBase = rtrim($this->ReadPropertyString('BaseURL'), '/');
        $host = $_SERVER['HTTP_HOST'] ?? '';
        if (is_string($host) && preg_match('/^[a-z0-9.\-:\[\]]+$/i', $host)) {
            $assetBase = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . $host;
        }
        $config['iconScriptURL'] = $assetBase . '/icons.js';
        if (($config['mode'] ?? '') === 'sdk' && $this->ReadPropertyBoolean('EmbedSDKIcons')) {
            $config['iconDefinitions'] = $this->compactIconDefinitions($config['messages'] ?? []);
        }
        $html = str_replace('__MZ_CONFIG__', json_encode($config, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_THROW_ON_ERROR), file_get_contents(__DIR__ . '/view.html'));
        return $html;
    }

    private function compactIconDefinitions(array $messages): array
    {
        $symbols = array_column($messages, 'icon');
        foreach (json_decode($this->ReadPropertyString('Rules'), true) ?? [] as $rule) {
            $symbols[] = $this->variableIcon((int) ($rule['VariableID'] ?? 0));
        }
        $symbols = array_unique(array_filter($symbols));
        if ($symbols === []) {
            return [];
        }
        $library = $this->embeddedIconLibrary();
        $definitions = [];
        foreach ($symbols as $raw) {
            if (!preg_match('/^[A-Za-z0-9 _-]{1,160}$/', $raw)) {
                continue;
            }
            $words = preg_split('/\\s+/', $raw);
            $name = end($words);
            foreach ($words as $word) {
                if (str_starts_with($word, 'fa-') && !in_array($word, ['fa-light', 'fa-brands', 'fa-kit'], true)) {
                    $name = $word;
                    break;
                }
            }
            $name = preg_replace('/^fa-/', '', $name);
            $names = array_unique([$name, strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $name)), strtolower($name)]);
            foreach ($names as $candidate) {
                // Read only the JSON icon tuple; never execute the downloaded JavaScript.
                $pattern = '~(?:^|[,{])"?' . preg_quote($candidate, '~') . '"?:(\\[\\d+,\\d+,\\[[^\\]]*\\],"[^"\\r\\n]*",(?:"[^"\\r\\n]*"|\\[[^\\]]*\\])\\])~';
                if (preg_match($pattern, $library, $match)) {
                    $tuple = json_decode($match[1], true);
                    if (is_array($tuple) && count($tuple) === 5 && is_numeric($tuple[0]) && is_numeric($tuple[1])) {
                        $definitions[$raw] = $tuple;
                        break;
                    }
                }
            }
        }
        return $definitions;
    }

    private function embeddedIconLibrary(): string
    {
        $port = max(1, min(65535, $this->ReadPropertyInteger('LocalWebPort')));
        // The entire instance buffer space is limited; symbol assets belong on disk.
        $directory = rtrim(sys_get_temp_dir(), '/\\') . '/symcon-visu-messages-' . substr(hash('sha256', IPS_GetKernelDir()), 0, 16);
        $file = $directory . '/icons-' . $port . '.cache';
        if (is_file($file) && !is_link($file)) {
            $packed = @file_get_contents($file);
            if (is_string($packed) && strlen($packed) <= 12001000) {
                $separator = strpos($packed, "\n");
                if ($separator !== false && $separator < 512) {
                    $meta = json_decode(substr($packed, 0, $separator), true);
                    $cached = @gzdecode(substr($packed, $separator + 1), 12000000);
                    if (is_array($meta) && is_string($cached) &&
                        strlen($cached) === ($meta['length'] ?? -1) &&
                        hash_equals((string) ($meta['hash'] ?? ''), hash('sha256', $cached))) {
                        return $cached;
                    }
                }
            }
        }
        $curl = curl_init('http://127.0.0.1:' . $port . '/icons.js');
        curl_setopt_array($curl, [CURLOPT_RETURNTRANSFER => true, CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_TIMEOUT => 4, CURLOPT_ENCODING => '']);
        $library = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        // Some installations return gzip bytes even without a Content-Encoding header.
        if (is_string($library) && str_starts_with($library, "\x1f\x8b")) {
            $library = gzdecode($library);
        }
        if ($status !== 200 || !is_string($library) || strlen($library) > 12000000 ||
            !str_contains($library, 'FontAwesome') || !str_contains($library, 'findIconDefinition') ||
            stripos($library, '</script') !== false) {
            $this->debug('Icon library', 'Lokaler Symbolsatz nicht erreichbar: HTTP ' . $status . ' ' . $error, 0);
            return '';
        }
        $compressed = gzencode($library, 6);
        if ($compressed !== false && !is_link($directory) &&
            (is_dir($directory) || @mkdir($directory, 0700, true))) {
            $meta = json_encode(['length' => strlen($library), 'hash' => hash('sha256', $library)], JSON_THROW_ON_ERROR);
            $temporary = @tempnam($directory, 'icons-');
            if ($temporary !== false) {
                @chmod($temporary, 0600);
                if (@file_put_contents($temporary, $meta . "\n" . $compressed, LOCK_EX) !== false) {
                    if (!@rename($temporary, $file)) {
                        @unlink($temporary);
                    }
                } else {
                    @unlink($temporary);
                }
            }
        }
        return $library;
    }

    private function appearance(): array
    {
        $colors = $this->ReadPropertyBoolean('CustomColors') ? $this->appearanceColors('') : null;
        $profiles = [];
        foreach (['tile' => 'Tile', 'webfront' => 'WebFront', 'ipsview' => 'IPSView'] as $key => $view) {
            $profiles[$key] = [
                'colors' => $this->ReadPropertyBoolean($view . 'OwnColors') ? $this->appearanceColors($view) : $colors,
                'css' => $this->ReadPropertyString($view . 'CSS'),
                'layout' => $this->ReadPropertyBoolean($view . 'OwnLayout') ? $this->layoutAppearance($view) : null
            ];
        }
        return array_merge($this->layoutAppearance(''), ['profiles' => $profiles,
            'sdkProfile' => $this->ReadPropertyString('SDKColorProfile'), 'colors' => $colors]);
    }

    private function appearanceColors(string $prefix): array
    {
        $colors = [];
        foreach (['bg' => 'BackgroundColor', 'card' => 'MessageColor', 'text' => 'TextColor', 'accent' => 'AccentColor'] as $key => $property) {
            $colors[$key] = sprintf('#%06x', max(0, min(0xffffff, $this->ReadPropertyInteger($prefix . $property))));
        }
        return $colors;
    }

    private function layoutAppearance(string $prefix): array
    {
        $sizes = ['mini' => [160, 320], 'compact' => [200, 480], 'normal' => [240, 760], 'large' => [280, 1100]];
        $size = $this->ReadPropertyString($prefix . 'MessageSize');
        $min = max(120, min(3000, $this->ReadPropertyInteger($prefix . 'MessageMinWidth')));
        $max = max($min, min(3000, $this->ReadPropertyInteger($prefix . 'MessageMaxWidth')));
        [$min, $max] = $size === 'custom' ? [$min, $max] : ($sizes[$size] ?? $sizes['normal']);
        return [
            'heading' => ['title' => $this->ReadPropertyString($prefix . 'HeadingTitle'), 'visible' => $this->ReadPropertyBoolean($prefix . 'ShowHeading')],
            'useVariableIcons' => $this->ReadPropertyBoolean($prefix . 'UseVariableIcons'),
            'minWidth' => $min, 'maxWidth' => $max,
            'minHeight' => max(0, min(2000, $this->ReadPropertyInteger($prefix . 'MessageMinHeight'))),
            'fontSize' => max(10, min(30, $this->ReadPropertyInteger($prefix . 'MessageFontSize'))),
            'metaFontSize' => max(9, min(20, $this->ReadPropertyInteger($prefix . 'MessageMetaFontSize'))),
            'iconSize' => max(24, min(96, $this->ReadPropertyInteger($prefix . 'IconSize'))),
            'cardStyle' => ['scaleContents' => $this->ReadPropertyBoolean($prefix . 'MessageScaleContents'),
                'borderColor' => sprintf('#%06x', max(0, min(0xffffff, $this->ReadPropertyInteger($prefix . 'MessageBorderColor')))),
                'borderWidth' => max(0, min(12, $this->ReadPropertyInteger($prefix . 'MessageBorderWidth'))),
                'radius' => max(0, min(100, $this->ReadPropertyInteger($prefix . 'MessageRadius'))),
                'shadow' => $this->ReadPropertyString($prefix . 'MessageShadow')]
        ];
    }

    private function needsVariableIcons(): bool
    {
        if ($this->ReadPropertyBoolean('UseVariableIcons')) {
            return true;
        }
        foreach (['Tile', 'WebFront', 'IPSView'] as $view) {
            if ($this->ReadPropertyBoolean($view . 'OwnLayout') && $this->ReadPropertyBoolean($view . 'UseVariableIcons')) {
                return true;
            }
        }
        return false;
    }

    public function UpdateAppearanceEditor(string $View): void
    {
        foreach (['common', 'tile', 'webfront', 'ipsview'] as $view) {
            $this->UpdateFormField('Appearance_' . $view, 'visible', $View === $view);
        }
    }

    private function messagesForVisualization(array $store): array
    {
        $icons = [];
        $needsIcons = $this->needsVariableIcons();
        foreach ($store['messages'] as &$message) {
            $id = (int) ($message['variableID'] ?? 0);
            if (!array_key_exists($id, $icons)) {
                $icons[$id] = $needsIcons ? $this->variableIcon($id) : '';
            }
            $message['icon'] = $icons[$id];
        }
        unset($message);
        return VisuMessagesRules::sorted($store['messages']);
    }

    private function variableIcon(int $id): string
    {
        if ($id <= 0 || !IPS_VariableExists($id)) {
            return '';
        }
        $icon = (string) (IPS_GetObject($id)['ObjectIcon'] ?? '');
        if ($icon !== '') {
            return $icon;
        }
        if (function_exists('IPS_GetVariablePresentation')) {
            $presentation = IPS_GetVariablePresentation($id);
            if (is_string($presentation['ICON'] ?? null) && $presentation['ICON'] !== '') {
                return $presentation['ICON'];
            }
        }
        $variable = IPS_GetVariable($id);
        $profile = $variable['VariableCustomProfile'] ?: $variable['VariableProfile'];
        if ($profile !== '' && IPS_VariableProfileExists($profile)) {
            return (string) (IPS_GetVariableProfile($profile)['Icon'] ?? '');
        }
        return '';
    }

    private function registerDataVariables(): void
    {
        $presentation = ['PRESENTATION' => VARIABLE_PRESENTATION_VALUE_PRESENTATION];
        $this->RegisterVariableInteger('MessageCount', 'Meldungen gesamt', $presentation, 20);
        $this->RegisterVariableInteger('PinnedCount', 'Meldungen angepinnt', $presentation, 30);
        $this->RegisterVariableInteger('UnpinnedCount', 'Meldungen nicht angepinnt', $presentation, 40);
        $this->RegisterVariableString('MessageData', 'Meldungsdaten (JSON)', $presentation, 50);
        $this->RegisterVariableInteger('LastUpdate', 'Letzte Aktualisierung', [
            'PRESENTATION' => VARIABLE_PRESENTATION_LEGACY, 'PROFILE' => '~UnixTimestamp'
        ], 60);
        $hidden = !$this->ReadPropertyBoolean('ShowModuleData');
        foreach (['URL', 'MessageCount', 'PinnedCount', 'UnpinnedCount', 'MessageData', 'LastUpdate'] as $ident) {
            IPS_SetHidden($this->GetIDForIdent($ident), $hidden);
        }
    }

    private function updateLinks(): void
    {
        $path = '/hook/visu-messages-' . $this->InstanceID . '?key=' . $this->ReadAttributeString('Token');
        $url = rtrim($this->ReadPropertyString('BaseURL'), '/') . $path;
        $this->SetValue('URL', $url);
        $height = max(200, min(2000, $this->ReadPropertyInteger('Height')));
        $this->SetValue('HTML', '<iframe title="Mitteilungszentrale" referrerpolicy="no-referrer" src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="width:100%;height:' . $height . 'px;border:0"></iframe>');
    }

    private function store(): array
    {
        return json_decode($this->ReadAttributeString('Store'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function save(array $store): void
    {
        $this->WriteAttributeString('Store', json_encode($store, JSON_THROW_ON_ERROR));
        $this->WriteAttributeInteger('UpdatedAt', time());
        $this->writeAnnouncementQueue($this->prunedAnnouncementQueue());
        $this->publish($store);
    }

    private function publish(array $store): void
    {
        $messages = $this->messagesForVisualization($store);
        $total = count($messages);
        $pinned = count(array_filter($messages, static fn(array $message): bool => (bool) ($message['pinned'] ?? false)));
        $updatedAt = $this->ReadAttributeInteger('UpdatedAt');
        $this->SetValue('LastUpdate', $updatedAt);
        $this->SetValue('MessageCount', $total);
        $this->SetValue('PinnedCount', $pinned);
        $this->SetValue('UnpinnedCount', $total - $pinned);
        $this->SetValue('MessageData', json_encode(['version' => 1, 'updatedAt' => $updatedAt, 'total' => $total, 'pinned' => $pinned,
            'unpinned' => $total - $pinned, 'messages' => $messages], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $this->UpdateVisualizationValue(json_encode(['messages' => $messages, 'appearance' => $this->appearance(), 'updatedAt' => $this->ReadAttributeInteger('UpdatedAt')], JSON_THROW_ON_ERROR));
    }

    private function debug(string $message, mixed $data, int $format = 0): void
    {
        if ($this->ReadPropertyBoolean('DebugEnabled')) {
            $this->SendDebug($message, is_string($data) ? $data : json_encode($data, JSON_THROW_ON_ERROR), $format);
        }
    }

    private function locked(callable $callback): void
    {
        $name = 'Visu-Messages.' . $this->InstanceID;
        if (!IPS_SemaphoreEnter($name, 5000)) {
            throw new RuntimeException('Mitteilungszentrale beschäftigt. Bitte erneut versuchen.');
        }
        try {
            $callback();
        } finally {
            IPS_SemaphoreLeave($name);
        }
    }

    public function GetConfigurationForm(): string
    {
        $form = json_decode(file_get_contents(__DIR__ . '/form.json'), true);
        $audioInstalled = in_array(self::ANNOUNCEMENT_MODULE, IPS_GetModuleList(), true);
        $audioReady = $audioInstalled && IPS_GetInstanceListByModuleID(self::ANNOUNCEMENT_MODULE) !== [];
        $options = [['caption' => 'Bitte Ziel-Visualisierung auswählen', 'value' => 0]];
        foreach ([self::TILE_MODULE => 'Kachelvisu', self::WEBFRONT_MODULE => 'WebFront'] as $module => $type) {
            foreach (IPS_GetInstanceListByModuleID($module) as $instance) {
                $options[] = ['caption' => $type . ' · ' . IPS_GetName($instance) . ' (#' . $instance . ')', 'value' => $instance];
            }
        }
        $disableInputs = static function (array &$items, bool $enabled) use (&$disableInputs): void {
            foreach ($items as &$item) {
                if (($item['type'] ?? '') !== 'Label' && isset($item['name'])) {
                    $item['enabled'] = $enabled;
                }
                if (isset($item['items'])) {
                    $disableInputs($item['items'], $enabled);
                }
            }
            unset($item);
        };
        foreach ($form['elements'][0]['form'] as &$section) {
            if (($section['caption'] ?? '') === 'Durchsage und Wiederholung') {
                $disableInputs($section['items'], $audioReady);
                foreach ($section['items'] as &$field) {
                    if (($field['name'] ?? '') === 'AnnouncementAvailabilityHint') {
                        $field['visible'] = !$audioReady;
                        $field['caption'] = !$audioInstalled
                            ? 'Das Modul Durchsage ist nicht installiert. Diese Option ist deshalb deaktiviert. Meldungsliste und andere Funktionen bleiben nutzbar.'
                            : 'Das Modul Durchsage ist installiert, aber es ist noch keine Durchsage-Instanz eingerichtet. Nach dem Anlegen das Formular neu öffnen.';
                    }
                }
                unset($field);
            }
            if (($section['caption'] ?? '') === 'Push-Benachrichtigung') {
                $pushReady = count($options) > 1;
                $disableInputs($section['items'], $pushReady);
                foreach ($section['items'] as &$field) {
                    if (($field['name'] ?? '') === 'PushVisuID') {
                        $field['options'] = $options;
                    }
                    if (($field['name'] ?? '') === 'PushAvailabilityHint') {
                        $field['visible'] = !$pushReady;
                    }
                }
                unset($field);
            }
        }
        unset($section);
        $editor = $this->ReadPropertyString('AppearanceEditor');
        if (!in_array($editor, ['common', 'tile', 'webfront', 'ipsview'], true)) {
            $editor = 'tile';
        }
        foreach ($form['elements'] as &$element) {
            if (($element['caption'] ?? '') === 'Einstellungen') {
                foreach ($element['items'] as &$panel) {
                    if (($panel['caption'] ?? '') === 'Darstellung je Visualisierung') {
                        foreach ($panel['items'] as &$group) {
                            if (str_starts_with($group['name'] ?? '', 'Appearance_')) {
                                $group['visible'] = $group['name'] === 'Appearance_' . $editor;
                            }
                        }
                        unset($group);
                    }
                }
                unset($panel);
            }
        }
        unset($element);
        $error = $this->GetBuffer('Error');
        if ($error !== '') {
            $form['elements'][] = ['type' => 'Label', 'caption' => 'Konfigurationsfehler: ' . $error];
        }
        $form['actions'][] = ['type' => 'Label', 'caption' => 'WebView-Adresse: ' . $this->GetValue('URL')];
        return json_encode($form, JSON_THROW_ON_ERROR);
    }
}
