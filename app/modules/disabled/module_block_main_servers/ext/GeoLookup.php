<?php

namespace app\modules\module_block_main_servers\ext;

use MaxMind\Db\Reader;

require_once __DIR__ . '/lib/autoload.php';

class GeoLookup
{
    private static ?Reader $reader = null;

    public static function databasePath(): string
    {
        return dirname(__DIR__) . '/geoip/GeoLite2-City.mmdb';
    }

    public static function fetchLatLon(string $ip): ?array
    {
        $ip = trim(explode(':', $ip)[0]);
        if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
            return null;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return null;
        }

        $reader = self::reader();
        if ($reader === null) {
            return null;
        }

        try {
            $record = $reader->get($ip);
        } catch (\Throwable $e) {
            return null;
        }

        if (!is_array($record)) {
            return null;
        }

        $lat = $record['location']['latitude'] ?? null;
        $lon = $record['location']['longitude'] ?? null;
        if ($lat === null || $lon === null) {
            return null;
        }

        return [
            'lat' => (float)$lat,
            'lon' => (float)$lon,
        ];
    }

    private static function reader(): ?Reader
    {
        if (self::$reader !== null) {
            return self::$reader;
        }

        $path = self::databasePath();
        if (!is_readable($path)) {
            return null;
        }

        try {
            self::$reader = new Reader($path);
        } catch (\Throwable $e) {
            return null;
        }

        return self::$reader;
    }
}
