<?php 

namespace app\modules\module_page_search\ext;

class Cache
{
    protected static $path              = __DIR__ . "/caches/";
    protected static $cache_file_data   = [];
    protected static $cache_file_name   = null;

    public static function put( string $key, $data, int $time = 3600 )
    {
        self::checkCacheData();

        self::$cache_file_data[$key] = [
            "value"         => $data,
            "time"          => $time,
            "time_create"   => \time()
        ];

        self::cacheRefresh();
    }
    
    public static function get( string $key, callable $CallbackIfNotExists = null )
    {
        self::checkCacheData();

        if( isset( self::$cache_file_data[$key] ) )
            return self::$cache_file_data[$key]["value"];

        $CallbackIfNotExists && $CallbackIfNotExists();
    }
    
    protected static function checkCacheData()
    {
        empty( self::$cache_file_name ) && self::init();
    }

    protected static function init()
    {
        $caches = glob(self::$path . "*.php");

        if( isset( $caches[0] ) )
        {
            self::$cache_file_data  = require $caches[0];
            self::$cache_file_name  = $caches[0];
        }
        else
        {
            self::$cache_file_name  = self::generateFileName() . ".php";
            self::cacheRefresh();
        }
        self::checkOldCache();
    }

    protected static function generateFileName() : string
    {
        return substr(str_shuffle(str_repeat("0123456789abcdefghijklmnopqrstuvwxyz", 10)), 0, 10);
    }

    protected static function checkOldCache()
    {
        if( !empty( self::$cache_file_data ) )
        {
            $cache_old = self::$cache_file_data;

            foreach( self::$cache_file_data as $key => $val )
            {
                if( ( $val["time_create"] + $val["time"] ) < \time() )
                    unset(self::$cache_file_data[$key]);
            }

            sizeof($cache_old) != sizeof(self::$cache_file_data) && self::cacheRefresh();
        }
    }

    protected static function cacheRefresh()
    {
        $fp = fopen(self::$path . basename(self::$cache_file_name) , 'w');
        fwrite($fp, '<?php return ' . var_export(self::$cache_file_data, true) . ';');
        fclose($fp);
    }
}