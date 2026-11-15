<?php
declare(strict_types=1);
require_once __DIR__ . '/Rules.php';

class VisuMessages extends IPSModuleStrict
{
    public function Create(): void
    {
        parent::Create();
        $this->RegisterPropertyString('Rules', '[]');
        $this->RegisterPropertyInteger('Limit', 200);
        $this->RegisterPropertyInteger('Height', 480);
        $this->RegisterPropertyString('BaseURL', '');
        $this->RegisterPropertyBoolean('ShowModuleData', false);
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
        $this->RegisterPropertyInteger('MessageBorderColor', 0x374652);
        $this->RegisterPropertyInteger('MessageBorderWidth', 1);
        $this->RegisterPropertyInteger('MessageRadius', 12);
        $this->RegisterPropertyString('MessageShadow', 'none');
        $this->RegisterAttributeString('Store', '{"next":1,"messages":[]}');
        $this->RegisterAttributeString('Token', '');
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
        $this->registerDataVariables();
        // Replace previously cleared colors with the original solid defaults once.
        $normalized = false;
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
            $rule['VariableID'] = $id;
            $rule['Operator'] = $op;
            $rule['Target'] = VisuMessagesRules::target((string) ($rule['Value'] ?? ''), $type);
            $active[] = $rule;
        }
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
                $text = strtr($rule['Text'], [
                    '{name}' => IPS_GetName($SenderID), '{value}' => $format($Data[0]),
                    '{old}' => $format($Data[2]), '{id}' => (string) $SenderID
                ]);
                array_unshift($store['messages'], [
                    'id' => $store['next']++, 'time' => time(), 'text' => $text,
                    'source' => IPS_GetName($SenderID), 'variableID' => $SenderID, 'pinned' => false
                ]);
                $changed = true;
            }
            if ($changed) {
                $store['messages'] = VisuMessagesRules::trim($store['messages'], $this->ReadPropertyInteger('Limit'));
                $this->save($store);
            }
        });
    }

    public function GetVisualizationTile(): string
    {
        return $this->page(['mode' => 'sdk', 'messages' => $this->messagesForVisualization($this->store())]);
    }

    public function RequestAction(string $Ident, mixed $Value): void
    {
        $this->SendDebug('Visualization action', json_encode(['action' => $Ident, 'value' => $Value]), 0);
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
            $this->SendDebug('Visualization error', $e->getMessage(), 0);
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
                'id' => $store['next']++, 'time' => time(), 'text' => 'Die Visu-Messages ist bereit.',
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
            echo json_encode(['messages' => $this->messagesForVisualization($this->store()), 'appearance' => $this->appearance()], JSON_THROW_ON_ERROR);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    private function page(array $config): string
    {
        $config['appearance'] = $this->appearance();
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
            $this->SendDebug('Icon library', 'Lokaler Symbolsatz nicht erreichbar: HTTP ' . $status . ' ' . $error, 0);
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
        $sizes = ['mini' => [160, 320], 'compact' => [200, 480], 'normal' => [240, 760], 'large' => [280, 1100]];
        $size = $this->ReadPropertyString('MessageSize');
        $min = max(120, min(3000, $this->ReadPropertyInteger('MessageMinWidth')));
        $max = max($min, min(3000, $this->ReadPropertyInteger('MessageMaxWidth')));
        [$min, $max] = $size === 'custom' ? [$min, $max] : ($sizes[$size] ?? $sizes['normal']);
        $colors = null;
        if ($this->ReadPropertyBoolean('CustomColors')) {
            $colors = [];
            foreach (['bg' => 'BackgroundColor', 'card' => 'MessageColor', 'text' => 'TextColor', 'accent' => 'AccentColor'] as $key => $property) {
                $value = $this->ReadPropertyInteger($property);
                $colors[$key] = sprintf('#%06x', max(0, min(0xffffff, $value)));
            }
        }
        $profiles = [];
        foreach (['webfront' => 'WebFront', 'ipsview' => 'IPSView', 'tile' => 'Tile'] as $key => $view) {
            $viewColors = $colors;
            if ($this->ReadPropertyBoolean($view . 'OwnColors')) {
                $viewColors = [];
                foreach (['bg' => 'BackgroundColor', 'card' => 'MessageColor', 'text' => 'TextColor', 'accent' => 'AccentColor'] as $color => $property) {
                    $value = $this->ReadPropertyInteger($view . $property);
                    $viewColors[$color] = sprintf('#%06x', max(0, min(0xffffff, $value)));
                }
            }
            $profiles[$key] = ['colors' => $viewColors, 'css' => $this->ReadPropertyString($view . 'CSS')];
        }
        return ['profiles' => $profiles, 'sdkProfile' => $this->ReadPropertyString('SDKColorProfile'), 'colors' => $colors, 'minWidth'  => $min, 'maxWidth' => $max,
            'cardStyle' => ['scaleContents' => $this->ReadPropertyBoolean('MessageScaleContents'),
                'borderColor' => sprintf('#%06x', max(0, min(0xffffff, $this->ReadPropertyInteger('MessageBorderColor')))),
                'borderWidth' => max(0, min(12, $this->ReadPropertyInteger('MessageBorderWidth'))),
                'radius' => max(0, min(100, $this->ReadPropertyInteger('MessageRadius'))),
                'shadow' => $this->ReadPropertyString('MessageShadow')],
            'minHeight' => max(0, min(2000, $this->ReadPropertyInteger('MessageMinHeight'))),
            'iconSize' => max(24, min(96, $this->ReadPropertyInteger('IconSize')))];
    }

    private function messagesForVisualization(array $store): array
    {
        $icons = [];
        foreach ($store['messages'] as &$message) {
            $id = (int) ($message['variableID'] ?? 0);
            if (!array_key_exists($id, $icons)) {
                $icons[$id] = $this->ReadPropertyBoolean('UseVariableIcons') ? $this->variableIcon($id) : '';
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
        $hidden = !$this->ReadPropertyBoolean('ShowModuleData');
        foreach (['URL', 'MessageCount', 'PinnedCount', 'UnpinnedCount', 'MessageData'] as $ident) {
            IPS_SetHidden($this->GetIDForIdent($ident), $hidden);
        }
    }

    private function updateLinks(): void
    {
        $path = '/hook/visu-messages-' . $this->InstanceID . '?key=' . $this->ReadAttributeString('Token');
        $url = rtrim($this->ReadPropertyString('BaseURL'), '/') . $path;
        $this->SetValue('URL', $url);
        $height = max(200, min(2000, $this->ReadPropertyInteger('Height')));
        $this->SetValue('HTML', '<iframe title="Visu-Messages" referrerpolicy="no-referrer" src="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '" style="width:100%;height:' . $height . 'px;border:0"></iframe>');
    }

    private function store(): array
    {
        return json_decode($this->ReadAttributeString('Store'), true, 512, JSON_THROW_ON_ERROR);
    }

    private function save(array $store): void
    {
        $this->WriteAttributeString('Store', json_encode($store, JSON_THROW_ON_ERROR));
        $this->publish($store);
    }

    private function publish(array $store): void
    {
        $messages = $this->messagesForVisualization($store);
        $total = count($messages);
        $pinned = count(array_filter($messages, static fn(array $message): bool => (bool) ($message['pinned'] ?? false)));
        $this->SetValue('MessageCount', $total);
        $this->SetValue('PinnedCount', $pinned);
        $this->SetValue('UnpinnedCount', $total - $pinned);
        $this->SetValue('MessageData', json_encode(['version' => 1, 'total' => $total, 'pinned' => $pinned,
            'unpinned' => $total - $pinned, 'messages' => $messages], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR));
        $this->UpdateVisualizationValue(json_encode(['messages' => $messages, 'appearance' => $this->appearance()], JSON_THROW_ON_ERROR));
    }

    private function locked(callable $callback): void
    {
        $name = 'Visu-Messages.' . $this->InstanceID;
        if (!IPS_SemaphoreEnter($name, 5000)) {
            throw new RuntimeException('Visu-Messages beschäftigt. Bitte erneut versuchen.');
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
        $error = $this->GetBuffer('Error');
        if ($error !== '') {
            $form['elements'][] = ['type' => 'Label', 'caption' => 'Konfigurationsfehler: ' . $error];
        }
        $form['actions'][] = ['type' => 'Label', 'caption' => 'WebView-Adresse: ' . $this->GetValue('URL')];
        return json_encode($form, JSON_THROW_ON_ERROR);
    }
}
