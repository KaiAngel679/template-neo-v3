<?php

namespace app\modules\module_block_main_servers\ext;

require_once __DIR__ . '/../../../ext/SourceQuery/bootstrap.php';
require_once __DIR__ . '/GeoLookup.php';
require_once __DIR__ . '/Rcon.php';
require_once __DIR__ . '/Modals/ExtendedModal.php';
require_once __DIR__ . '/Modals/Modal.php';

use xPaw\SourceQuery\SourceQuery;

use Exception;

class ServerInfoManager
{
    private int $cacheTime;
    private string $cacheDir;
    private string $mapPinsDir;
    private string $mapsDir;
    private object $General;
    private object $Translate;
    private object $Db;
    private string $logFile;
    private int $tmpTime = 300;
    private int $logMaxSize = 2097152;
    private array $pendingRefreshes = [];
    private bool $shutdownRegistered = false;
    private bool $listDidSyncFetch = false;

    private array $settings;
    private int $offlineCacheTime = 120;
    private int $offlineRefreshLockTime = 90;
    private ?array $modsJsonCache = null;
    private array $geoMemoryCache = [];
    private array $serverDataMemoryCache = [];
    private bool $listForceRefresh = false;

    public function __construct(object $General, object $Translate, object $Db, int $cachetime = 30)
    {
        $this->General   = $General;
        $this->Translate = $Translate;
        $this->Db        = $Db;
        $this->cacheTime  = $cachetime;
        $this->cacheDir  = __DIR__ . '/../servers/';
        $this->mapPinsDir = $_SERVER['DOCUMENT_ROOT'] . '/storage/cache/img/pins/maps/';
        $this->mapsDir    = $_SERVER['DOCUMENT_ROOT'] . '/storage/cache/img/maps/';
        !is_dir($this->cacheDir) && mkdir($this->cacheDir, 0777, true);
        $this->logFile = __DIR__ . '/../logs/logs.log';
        !is_dir(dirname($this->logFile)) && mkdir(dirname($this->logFile), 0777, true);
        $this->settings = $this->getSettings();
    }
    public function getServerData(array $server, bool $forcePost = false, bool $skipMaintenance = false): array
    {
        if (!$skipMaintenance) {
            $this->cleanTmp();
            $this->rotateLogs();
        }
        $ipPort = $server['ip'];
        $cacheKey = str_replace('.', '_', $ipPort);
        $cached = $this->readCache($cacheKey);
        $now = time();
        $valid = $this->isValidModalCache($cached, $now, $server);

        if ($valid && !$forcePost) {
            $this->log($server, 'cache', 'cache_valid');
            return $cached;
        }

        if (!empty($cached) && $this->isListCacheUsable($cached) && !$forcePost) {
            if ($this->needsExtendedData($server) && ($cached['type'] ?? '') !== 'extended') {
                return $this->fetchServerData($server, $cacheKey);
            }
            $this->scheduleBackgroundRefresh($server, $cacheKey, $cached);
            $this->log($server, 'cache', 'cache_stale_returned');
            return $cached;
        }

        return $this->fetchServerData($server, $cacheKey);
    }

    private function fetchServerData(array $server, string $cacheKey): array
    {
        $lockFile = $this->cacheDir . $cacheKey . '.fetch.lock';
        $fp = @fopen($lockFile, 'c+');
        if ($fp === false) {
            return $this->performServerFetch($server, $cacheKey);
        }

        try {
            $waitUntil = time() + 8;
            $now = time();
            while (!flock($fp, LOCK_EX | LOCK_NB)) {
                $cached = $this->readCache($cacheKey);
                if ($this->isFreshServerCache($cached, $now, $server)) {
                    return $cached;
                }
                if (time() >= $waitUntil) {
                    break;
                }
                usleep(150000);
            }

            if (!flock($fp, LOCK_EX)) {
                $cached = $this->readCache($cacheKey);
                if ($this->isFreshServerCache($cached, $now, $server)) {
                    return $cached;
                }
                return $this->performServerFetch($server, $cacheKey);
            }

            $now = time();
            $cached = $this->readCache($cacheKey);
            if ($cached !== null && $this->isValidModalCache($cached, $now, $server)) {
                return $cached;
            }
            return $this->performServerFetch($server, $cacheKey);
        } finally {
            @flock($fp, LOCK_UN);
            @fclose($fp);
            @unlink($lockFile);
        }
    }

    private function performServerFetch(array $server, string $cacheKey): array
    {
        if ($this->hasRcon($server)) {
            $rconData = $this->fetchViaRcon($server, $cacheKey);
            if ($rconData !== null) {
                $this->writeCache($cacheKey, $rconData);
                return $rconData;
            }
            $this->log($server, 'fetch', 'rcon_exhausted', ['next' => 'sourcequery']);
        }

        $basic = $this->SourceQuery($server);
        $previous = $this->readCache($cacheKey);

        if (
            $this->hasRcon($server)
            && !empty($basic['offline'])
            && $this->shouldKeepRconCacheOverSourceQueryOffline($cacheKey, $previous)
        ) {
            $kept = $this->withCacheExpiry($previous);
            $this->writeCache($cacheKey, $kept);
            $this->log($server, 'sourcequery', 'sourcequery_fallback_offline_kept_rcon');
            return $kept;
        }

        $this->writeCache($cacheKey, $basic);
        $this->log($server, 'sourcequery', $this->hasRcon($server) ? 'sourcequery_fallback' : 'sourcequery_no_rcon');
        return $basic;
    }

    private function hasRcon(array $server): bool
    {
        return !empty($server['rcon']);
    }

    private function shouldKeepRconCacheOverSourceQueryOffline(string $cacheKey, ?array $cached): bool
    {
        if (empty($cached) || ($cached['type'] ?? '') !== 'extended' || !empty($cached['offline'])) {
            return false;
        }
        $fileAge = $this->serverCacheFileAge($cacheKey);
        return $fileAge !== null && $fileAge < $this->offlineCacheTime;
    }

    private function fetchViaRcon(array $server, string $cacheKey): ?array
    {
        if (!$this->hasRcon($server)) {
            return null;
        }

        $game = $server['server_game'] ?? '';

        if (!empty($this->settings['password']) && in_array($game, ['cs2', 'csgo'], true)) {
            $tmpLog = $game === 'csgo' ? 'sm_getserverinfo' : 'mm_postpush';
            $fromTmp = $this->normalizeRconPayload(
                $server,
                $this->tryRconPost($server, $cacheKey, $game),
                $tmpLog
            );
            if ($fromTmp !== null) {
                return $fromTmp;
            }
        }

        foreach ($this->rconDirectCommands($game) as $command => $logType) {
            $fromCmd = $this->fetchExtendedFromRconCommand($server, $command, $logType);
            if ($fromCmd !== null) {
                return $fromCmd;
            }
        }

        return null;
    }

    private function rconDirectCommands(string $game): array
    {
        if ($game === 'csgo') {
            return ['sm_getserverinfo' => 'sm_getserverinfo'];
        }
        if ($game === 'cs2') {
            return ['mm_getinfo' => 'mm_getinfo'];
        }
        return [
            'mm_getinfo' => 'mm_getinfo',
            'sm_getserverinfo' => 'sm_getserverinfo',
        ];
    }

    private function fetchExtendedFromRconCommand(array $server, string $command, string $logType): ?array
    {
        $rconRaw = $this->tryRconCommand($server, $command, $logType);
        if (!$rconRaw) {
            return null;
        }
        $data = $this->ExtendedData($server, $rconRaw);
        $data['type'] = 'extended';
        $this->log($server, 'fetch', 'ok', ['via' => 'rcon', 'cmd' => $logType]);
        return $data;
    }

    private function normalizeRconPayload(array $server, ?array $postRaw, string $logType): ?array
    {
        if (!$postRaw) {
            return null;
        }

        if (isset($postRaw['data']['current_map'])) {
            $data = $postRaw;
            $data['type'] = 'extended';
            $this->log($server, 'fetch', 'ok', ['via' => 'tmp', 'cmd' => $logType]);
            return $this->withCacheExpiry($data);
        }

        if (isset($postRaw['current_map'])) {
            $data = $this->ExtendedData($server, $postRaw);
            $data['type'] = 'extended';
            $this->log($server, 'fetch', 'ok', ['via' => 'tmp', 'cmd' => $logType]);
            return $data;
        }

        $this->log($server, $logType, 'push_invalid_format');
        return null;
    }

    private function cacheExpiryFor(array $data): int
    {
        if (($data['type'] ?? '') === 'extended') {
            return $this->cacheTime;
        }
        if (!empty($data['offline'])) {
            return $this->offlineCacheTime;
        }
        return $this->cacheTime;
    }

    private function withCacheExpiry(array $data): array
    {
        $data['time'] = time() + $this->cacheExpiryFor($data);
        return $data;
    }

    private function scheduleBackgroundRefresh(array $server, string $cacheKey, ?array $cached = null): void
    {
        $lockFile = $this->cacheDir . $cacheKey . '.lock';
        $lockTtl = (!empty($cached['offline'])) ? $this->offlineRefreshLockTime : $this->cacheTime;
        if (file_exists($lockFile) && (time() - filemtime($lockFile)) < $lockTtl) {
            return;
        }

        foreach ($this->pendingRefreshes as $task) {
            if ($task['cacheKey'] === $cacheKey) {
                return;
            }
        }

        $this->pendingRefreshes[] = [
            'server' => $server,
            'cacheKey' => $cacheKey,
        ];

        if (!$this->shutdownRegistered) {
            $this->shutdownRegistered = true;
            $self = $this;
            register_shutdown_function(function () use ($self) {
                $self->processBackgroundRefreshes();
            });
        }
    }

    public function processBackgroundRefreshes(): void
    {
        if (empty($this->pendingRefreshes)) {
            return;
        }

        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }

        foreach ($this->pendingRefreshes as $task) {
            $lockFile = $this->cacheDir . $task['cacheKey'] . '.lock';
            @file_put_contents($lockFile, (string)time());
            try {
                $this->performServerFetch($task['server'], $task['cacheKey']);
                $this->log($task['server'], 'background', 'refresh_ok');
            } catch (\Throwable $e) {
                $this->log($task['server'], 'background', 'refresh_error', ['msg' => $e->getMessage()]);
            } finally {
                @unlink($lockFile);
            }
        }
        $this->pendingRefreshes = [];
        $this->shutdownRegistered = false;
        $this->markRconWaveDone();
    }

    private function markRconWaveDone(): void
    {
        $waveLock = __DIR__ . '/../temp/servers.rcon.wave.lock';
        @file_put_contents($waveLock, (string)time(), LOCK_EX);
    }

    private function serverCacheFileAge(string $cacheKey): ?int
    {
        $file = $this->cacheDir . $cacheKey . '.json';
        if (!file_exists($file)) {
            return null;
        }
        $mtime = filemtime($file);
        return $mtime !== false ? time() - $mtime : null;
    }

    private function getRconWaveCooldown(): int
    {
        return max(1, $this->cacheTime);
    }

    private function logWave(string $status, array $extra = []): void
    {
        $this->log(['ip' => 'list'], 'cache', $status, $extra);
    }

    private function lastRconWaveAge(int $now): ?int
    {
        $waveLock = __DIR__ . '/../temp/servers.rcon.wave.lock';
        if (!file_exists($waveLock)) {
            return null;
        }
        $ts = (int)trim((string)file_get_contents($waveLock));
        if ($ts > 0) {
            return $now - $ts;
        }
        $mtime = filemtime($waveLock);
        return $mtime !== false ? $now - $mtime : null;
    }

    private function scheduleListRefreshWaveIfNeeded(array $servers, int $now): void
    {
        $cooldown = $this->getRconWaveCooldown();
        $waveAge = $this->lastRconWaveAge($now);
        if ($waveAge !== null && $waveAge < $cooldown) {
            $this->logWave('cache_wave_skipped', ['wave_age' => $waveAge, 'cooldown' => $cooldown]);
            return;
        }

        $queued = false;
        foreach ($servers as $srv) {
            $cacheKey = str_replace('.', '_', $srv['ip']);
            $cached = $this->readCache($cacheKey);
            if ($this->isFreshServerCache($cached, $now, $srv)) {
                continue;
            }
            $fileAge = $this->serverCacheFileAge($cacheKey);
            if ($fileAge !== null && $fileAge < $cooldown) {
                continue;
            }
            $before = count($this->pendingRefreshes);
            $this->scheduleBackgroundRefresh($srv, $cacheKey, $cached);
            if (count($this->pendingRefreshes) > $before) {
                $queued = true;
            }
        }

        if ($queued) {
            $this->logWave('cache_wave_started', ['cooldown' => $cooldown]);
            $this->markRconWaveDone();
        } else {
            $this->logWave('cache_wave_skipped', ['reason' => 'all_servers_fresh']);
        }
    }

    public function getServersData(array $servers, bool $forceRefresh = false): array
    {
        $this->cleanTmp();
        $this->rotateLogs();
        $this->serverDataMemoryCache = [];
        $this->listForceRefresh = $forceRefresh;
        $this->listDidSyncFetch = false;
        $servers = $this->dedupeServersByIp($servers);

        $data = [
            'servers' => [],
            'mods' => [],
            'time' => time() + $this->cacheTime,
        ];

        $serverSubmodMap = [];
        foreach ($this->getModsJson() as $mod) {
            $modTitle = $mod['title'] ?? ($mod['name'] ?? null);

            if ($modTitle) {
                $data['mods'][$modTitle] = 0;
            }

            foreach ($mod['submods'] ?? [] as $submod) {
                foreach ($submod['servers'] ?? [] as $sid) {
                    $serverSubmodMap[(string)$sid] = $submod['title'];
                }
            }
        }

        $now = time();
        foreach ($servers as $i => $srv) {
            $server = $this->resolveServerDataForList($srv, $now);
            $geo = $this->getServerGeo($srv['ip']);
            $serverId = (string)($srv['id'] ?? '');

            $data['servers'][] = [
                'id' => $srv['id'] ?? ($i + 1),
                'ip' => $srv['fakeip'] ?? ($srv['ip'] ?? ''),
                'HostName' => $srv['name_custom'] ?? '',
                'Map' => $server['Map'] ?? '-',
                'MapImage' => $server['MapImage'] ?? '',
                'Players' => $server['Players'] ?? 0,
                'MaxPlayers' => $server['MaxPlayers'] ?? 0,
                'Mod' => $server['Mod'] ?? ($srv['server_mod'] ?? 0),
                'GameMode' => $srv['server_mod'] ?? '',
                'Bage' => $srv['server_bage'] ?? '',
                'game' => $srv['server_game'] ?? '',
                'lat' => $geo['lat'] ?? null,
                'lon' => $geo['lon'] ?? null,
                'Location' => trim($srv['server_city'] ?? ''),
                'Country' => strtolower(trim($srv['server_country'] ?? '')),
                'Category' => $serverSubmodMap[$serverId] ?? '',
                'techwork' => $srv['server_status'] == 2,
            ];

            $modName = $srv['server_mod'] ?? 'unknown';

            if (!isset($data['mods'][$modName])) {
                $data['mods'][$modName] = 0;
            }

            $data['mods'][$modName] += (int)($server['Players'] ?? 0);
        }

        $data['servers'] = array_values($data['servers']);
        $data['time'] = time() + $this->cacheTime;
        if (!$forceRefresh) {
            if ($this->listDidSyncFetch) {
                $this->markRconWaveDone();
                $this->logWave('cache_wave_marked', ['reason' => 'list_sync_fetch']);
            } else {
                $this->scheduleListRefreshWaveIfNeeded($servers, $now);
            }
        }
        $this->listForceRefresh = false;
        $this->listDidSyncFetch = false;

        return $data;
    }

    private function dedupeServersByIp(array $servers): array
    {
        $seen = [];
        $result = [];
        foreach ($servers as $srv) {
            $ip = $srv['ip'] ?? '';
            if ($ip === '' || isset($seen[$ip])) {
                continue;
            }
            $seen[$ip] = true;
            $result[] = $srv;
        }
        return $result;
    }

    private function resolveServerDataForList(array $server, int $now): array
    {
        $cacheKey = str_replace('.', '_', $server['ip']);
        if (isset($this->serverDataMemoryCache[$cacheKey])) {
            return $this->serverDataMemoryCache[$cacheKey];
        }

        if (!$this->listForceRefresh) {
            $cached = $this->readCache($cacheKey);
            if ($this->isFreshServerCache($cached, $now, $server)) {
                $this->serverDataMemoryCache[$cacheKey] = $cached;
                return $cached;
            }
            if ($this->isListCacheUsable($cached)) {
                $this->log($server, 'cache', 'cache_list_reused');
                $this->serverDataMemoryCache[$cacheKey] = $cached;
                return $cached;
            }
        }

        $fresh = $this->fetchServerData($server, $cacheKey);
        $this->listDidSyncFetch = true;
        $this->serverDataMemoryCache[$cacheKey] = $fresh;
        return $fresh;
    }

    private function isFreshServerCache(?array $cached, int $now, array $server): bool
    {
        return $this->isListCacheUsable($cached) && $this->isValidModalCache($cached, $now, $server);
    }

    private function isListCacheUsable(?array $cached): bool
    {
        if (empty($cached)) {
            return false;
        }
        return array_key_exists('Players', $cached)
            && array_key_exists('MaxPlayers', $cached)
            && array_key_exists('Map', $cached);
    }

    private function getModsJson(): array
    {
        if ($this->modsJsonCache !== null) {
            return $this->modsJsonCache;
        }
        $modsJsonPath = MODULES . 'module_page_mon_settings/mods.json';
        if (!file_exists($modsJsonPath)) {
            $this->modsJsonCache = [];
            return $this->modsJsonCache;
        }
        $this->modsJsonCache = json_decode(file_get_contents($modsJsonPath), true) ?? [];
        return $this->modsJsonCache;
    }

    private function needsExtendedData(array $server): bool
    {
        return $this->hasRcon($server)
            && in_array($server['server_game'] ?? '', ['cs2', 'csgo'], true);
    }

    private function isValidModalCache(?array $cached, int $now, array $server): bool
    {
        if (empty($cached) || !isset($cached['time']) || $cached['time'] < $now) {
            return false;
        }
        if ($this->needsExtendedData($server)) {
            return ($cached['type'] ?? '') === 'extended';
        }
        $type = $cached['type'] ?? '';
        return $type === 'default' || $type === 'extended';
    }

    private function tryRconCommand(array $server, string $command, string $logType): ?array
    {
        $ipParts = explode(':', $server['ip']);
        $ip = $ipParts[0] ?? null;
        $port = $ipParts[1] ?? null;
        $password = $server['rcon'] ?? null;
        if (!$ip || !$port || empty($password)) {
            return null;
        }

        try {
            $rcon = new Rcon($ip, (int)$port);
            if (!$rcon->Connect()) {
                return null;
            }
            $rcon->RconPass($password);
            $response = $rcon->Command($command);
            $rcon->Disconnect();
            if (!$response) {
                return null;
            }
            $data = json_decode($response, true);
            if (!is_array($data) || json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }
            return $data;
        } catch (Exception $e) {
            $this->log($server, $logType, 'rcon_error', ['msg' => $e->getMessage()]);
            return null;
        }
    }
    private function tryRconPost(array $server, string $cacheKey, string $game): ?array
    {
        $ipParts = explode(':', $server['ip']);
        $ip = $ipParts[0] ?? null;
        $port = $ipParts[1] ?? null;
        $password = $server['rcon'] ?? null;
        $command = $game === 'csgo' ? 'sm_getserverinfo' : 'mm_postpush';
        if (!$ip || !$port || empty($password)) return null;
        try {
            $rcon = new Rcon($ip, (int)$port);
            if (!$rcon->Connect()) return null;
            $rcon->RconPass($password);

            $rcon->Command($command);
            $rcon->Disconnect();

            $tmpFile = $this->cacheDir . $cacheKey . '.tmp';
            $pushed = null;
            for ($i = 0; $i < 6; $i++) {
                if (!file_exists($tmpFile)) {
                    usleep(100000);
                    continue;
                }
                $raw = file_get_contents($tmpFile);
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $pushed = $decoded;
                    @unlink($tmpFile);
                    break;
                }
                usleep(100000);
            }
            if (!$pushed) {
                $this->log($server, $command, 'tmp_missing');
            }
            return $pushed;
        } catch (Exception $e) {
            $this->log($server, $command, 'push_error', ['msg' => $e->getMessage()]);
            return null;
        }
    }

    private function log(array $server, string $type, string $status, array $extra = []): void
    {
        if (!isset($this->settings['debug']) || empty($this->settings['debug'])) return;
        if (empty($this->logFile)) return;
        $ip = $server['ip'] ?? 'unknown';
        $line = [
            'time'     => date('Y-m-d H:i:s'),
            'ip'       => $ip,
            'type' => $type,
            'status'   => $status,
        ] + $extra;
        $json = json_encode($line, JSON_UNESCAPED_UNICODE);
        @file_put_contents($this->logFile, $json . PHP_EOL, FILE_APPEND);
    }
    private function rotateLogs(): void
    {
        if (empty($this->logFile) || !file_exists($this->logFile)) return;
        $size = filesize($this->logFile);
        if ($size === false || $size < $this->logMaxSize) return;
        $archive = $this->logFile . '.' . date('Ymd_His');
        @rename($this->logFile, $archive);
        @file_put_contents($this->logFile, "");
    }

    private function cleanTmp(): void
    {
        if (!is_dir($this->cacheDir)) return;
        $now = time();
        foreach (glob($this->cacheDir . '*.tmp') as $file) {
            $mtime = filemtime($file);
            if ($mtime !== false && ($now - $mtime) > $this->tmpTime) {
                @unlink($file);
            }
        }
        foreach (glob($this->cacheDir . '*.fetch.lock') ?: [] as $file) {
            $mtime = filemtime($file);
            if ($mtime !== false && ($now - $mtime) > $this->offlineRefreshLockTime) {
                @unlink($file);
            }
        }
    }

    private function SourceQuery(array $server, bool $light = false): array
    {
        $ipParts = explode(':', $server['ip']);
        $ip = $ipParts[0] ?? null;
        $port = $ipParts[1] ?? null;
        $Query = new SourceQuery();
        $fallback = $this->OfflineData($server, $light);
        try {
            $Query->Connect($ip, $port, 1, SourceQuery::SOURCE);
            $info = $Query->GetInfo();
            $mapName = basename($info['Map']);
            $appId = $info['GameID'] ?? 730;
            $images = $this->MapImages($mapName, $appId);
            $players = [];
            if (!$light) {
                try {
                    $players = $Query->GetPlayers();
                } catch (Exception $e) {
                }
            }
            $rendered = $light ? $this->defaultModalData() : $this->renderDefaultModal($players);
            $structure = [
                'id'    => $server['id'] ?? 0,
                'Ip'        => $server['fakeip'] ?? $server['ip'],
                'Map'       => $mapName,
                'MapPin'    => $images['pin'],
                'MapImage'  => $images['image'],
                'HostName'  => $server['name_custom'] ?? ($server['name'] ?? ''),
                'Players'   => $info['Players'],
                'MaxPlayers' => $info['MaxPlayers'],
                'Mod'       => $appId,
                'GameMode'  => $server['server_mod'] ?? '',
                'Bage'      => $server['server_bage'] ?? '',
                'type'      => 'default',
                'data'      => $rendered,
                'techwork' => $server['server_status'] == 2 ? true : false,
            ];
            return $this->withCacheExpiry($structure);
        } catch (Exception $e) {
            return $fallback;
        } finally {
            $Query->Disconnect();
        }
    }

    private function ExtendedData(array $server, array $extended): array
    {
        $mapName = $extended['current_map'] ?? ($extended['current_map_name'] ?? '-');
        $appId = $server['server_game'] === 'csgo' ? 4465480 : 730;
        $images = $this->MapImages($mapName, $appId);
        $rendered = $this->renderExtendedModal($extended, $server);
        $data = [
            'id'    => $server['id'] ?? 0,
            'Ip'        => $server['fakeip'] ?? $server['ip'],
            'Map'       => $mapName,
            'MapPin'    => $images['pin'],
            'MapImage'  => $images['image'],
            'HostName'  => $server['name_custom'] ?? ($server['name'] ?? ''),
            'Players'   => $extended['players_count'] ?? count($extended['players'] ?? []),
            'MaxPlayers' => $extended['max_players'] ?? 32,
            'Mod'       => $appId,
            'GameMode'  => $server['server_mod'] ?? '',
            'Bage'      => $server['server_bage'] ?? '',
            'type'      => 'extended',
            'data'      => $rendered,
            'techwork' => $server['server_status'] == 2 ? true : false,
        ];
        return $this->withCacheExpiry($data);
    }

    private function OfflineData(array $server, bool $light = false): array
    {
        $appId = $server['server_game'] === 'csgo' ? 4465480 : 730;
        $images = $this->MapImages('-', $appId);
        $rendered = $light ? $this->defaultModalData() : $this->renderDefaultModal([]);
        $data = [
            'id'    => $server['id'] ?? 0,
            'Ip'        => $server['fakeip'] ?? $server['ip'],
            'Map'       => '',
            'MapPin'    => $images['pin'],
            'MapImage'  => $images['image'],
            'HostName'  => $this->Translate->get_translate_module_phrase('module_block_main_servers', '_serverDown'),
            'Players'   => 0,
            'MaxPlayers' => 0,
            'Mod'       => $appId,
            'GameMode'  => $server['server_mod'] ?? '',
            'Bage'      => $server['server_bage'] ?? '',
            'type'      => 'default',
            'data'      => $rendered,
            'offline'   => true,
            'techwork' => $server['server_status'] == 2 ? true : false,
        ];
        return $this->withCacheExpiry($data);
    }

    private function defaultModalData(): array
    {
        return [
            'current_map' => '-',
            'players'     => [],
            'score_ct'    => 0,
            'score_t'     => 0,
            'time'        => time(),
            'winteam'     => '',
            'admins'      => 0,
        ];
    }

    private function renderExtendedModal(array $data, array $server): array
    {
        if (class_exists(Modals\ExtendedModal::class)) {
            $Modal = new Modals\ExtendedModal($this->General, $this->Db, $this->Translate);
            return $Modal->Render($data, $server);
        }
        return $data;
    }

    private function renderDefaultModal(array $players): array
    {
        if (class_exists(Modals\Modal::class)) {
            $Modal = new Modals\Modal($this->Translate);
            return $Modal->Render($players);
        }
        return $this->defaultModalData();
    }

    private function MapImages(string $mapName, int $appId): array
    {
        $mapBasePath = $this->mapsDir . $appId . '/';
        $mapBaseUrl  = '/storage/cache/img/maps/' . $appId . '/';
        $image = '';
        foreach (["{$mapName}.webp", "{$mapName}.jpg", "-.webp", "-.jpg"] as $file) {
            if (file_exists($mapBasePath . $file)) {
                $image = $mapBaseUrl . $file;
                break;
            }
        }
        $pinBasePath = $this->mapPinsDir;
        $pinBaseUrl  = '/storage/cache/img/pins/maps/';
        $pin = '';
        foreach (["{$mapName}.webp", "{$mapName}.jpg", 'default.webp', 'default.jpg'] as $file) {
            if (file_exists($pinBasePath . $file)) {
                $pin = $pinBaseUrl . $file;
                break;
            }
        }
        return ['image' => $image, 'pin' => $pin, 'map_name' => $mapName, 'app_id' => $appId, 'map_url' => $mapBaseUrl . $mapName];
    }

    private function readCache(string $key): ?array
    {
        $file = $this->cacheDir . $key . '.json';
        if (!file_exists($file)) return null;
        $json = file_get_contents($file);
        $data = json_decode($json, true);
        return is_array($data) ? $data : null;
    }

    private function writeCache(string $key, array $data): void
    {
        $data = $this->withCacheExpiry($data);
        $file = $this->cacheDir . $key . '.json';
        $json = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        }
        if ($json !== false && $json !== '') {
            file_put_contents($file, $json);
        }
    }

    private function getServerGeo(string $ip): ?array
    {
        $ipOnly = explode(':', $ip)[0];
        $cacheKey = 'geo_' . str_replace('.', '_', $ipOnly);

        if (isset($this->geoMemoryCache[$cacheKey])) {
            return $this->geoMemoryCache[$cacheKey];
        }

        $cacheFile = $this->cacheDir . $cacheKey . '.json';

        if (file_exists($cacheFile)) {
            $cached = json_decode(file_get_contents($cacheFile), true);
            if (is_array($cached) && isset($cached['expire']) && $cached['expire'] > time()) {
                $this->geoMemoryCache[$cacheKey] = $cached;
                return $cached;
            }
        }

        $coords = GeoLookup::fetchLatLon($ipOnly);
        if ($coords === null) {
            return null;
        }

        $result = [
            'lat'    => $coords['lat'],
            'lon'    => $coords['lon'],
            'expire' => time() + 3600,
        ];
        file_put_contents($cacheFile, json_encode($result));
        $this->geoMemoryCache[$cacheKey] = $result;
        return $result;
    }

    private function getSettings(): array
    {
        if (file_exists(MODULES . 'module_page_mon_settings/settings.php')) {
            return require MODULES . 'module_page_mon_settings/settings.php';
        } else {
            return [];
        }
    }
}
