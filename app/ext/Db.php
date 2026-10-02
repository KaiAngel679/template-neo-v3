<?php

namespace app\ext;

use PDO;
use PDOException;

class Db
{
    private $options = [];
    private $db = [];
    private $dns = [];
    protected $pdo = [];
    public $db_data = [];
    private $table_count_for = [];
    public $mod_count = 0;
    public $user_count = [];
    public $db_count = [];
    public $table_count = [];
    public $mod_name = [];
    public $table_statistics_count = 0;
    public $support_statistics = ['LevelsRanks'];
    public $statistics_table = [];
    public function __construct()
    {

        defined('IN_LR') != true && die();

        $this->db = $this->get_db_options();

        $this->options = [
            PDO::ATTR_ERRMODE,
            PDO::ERRMODE_SILENT,
            PDO::MYSQL_ATTR_USE_BUFFERED_QUERY,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ];

        $this->table_count['LevelsRanks'] = 0;

        $this->mod_count = sizeof($this->db);

        for ($m = 0; $m < $this->mod_count; $m++) {

            $this->mod_name[] = array_keys($this->db)[$m];

            $this->user_count[$this->mod_name[$m]] = sizeof($this->db[$this->mod_name[$m]]);

            for ($u = 0; $u < $this->user_count[$this->mod_name[$m]]; $u++) {

                $this->db_count[$this->mod_name[$m]][$u] = sizeof($this->db[$this->mod_name[$m]][$u]['DB']);

                for ($d = 0; $d < $this->db_count[$this->mod_name[$m]][$u]; $d++) {

                    $this->db[$this->mod_name[$m]][$u]["PORT"] = empty($this->db[$this->mod_name[$m]][$u]["PORT"]) ? 3306 : $this->db[$this->mod_name[$m]][$u]["PORT"];

                    $this->dns[$this->mod_name[$m]][$u][$d] = 'mysql:host=' . $this->db[$this->mod_name[$m]][$u]["HOST"] . ';port=' . $this->db[$this->mod_name[$m]][$u]["PORT"] . ';dbname=' . $this->db[$this->mod_name[$m]][$u]['DB'][$d]['DB'] . ';charset=utf8';

                    $this->table_count_for[$this->mod_name[$m]] = sizeof($this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix']);

                    for ($t = 0; $t < $this->table_count_for[$this->mod_name[$m]]; $t++) {

                        $rank_pack = empty($this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['ranks_pack']) ? 'default' : $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['ranks_pack'];

                        $this->db_data[$this->mod_name[$m]][] = [
                            'DB_mod' => $this->mod_name[$m],
                            'USER_ID' => $u,
                            'USER' => $this->db[$this->mod_name[$m]][$u]['USER'],
                            'DB' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['DB'],
                            'DB_num' => $d,
                            'Table' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['table'] ?? '',
                            'table_id' => $t,
                            'name' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['name'] ?? 'Unnamed',
                            'mod' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['mod'] ?? 730,
                            'steam' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['steam'] ?? 1,
                            'ranks_pack' => $rank_pack ?? 'default'
                        ];
                        in_array($this->mod_name[$m], $this->support_statistics) && $this->statistics_table[] = ['DB_mod' => $this->mod_name[$m], 'name' => $this->db[$this->mod_name[$m]][$u]['DB'][$d]['Prefix'][$t]['name'], 'ranks_pack' => $rank_pack];
                    }
                }
            }

            $this->table_count[$this->mod_name[$m]] = sizeof($this->db_data[$this->mod_name[$m]]);
        }

        $this->table_statistics_count = $this->table_count['LevelsRanks'];
    }
    
    private function get_db_options()
    {
        $db = file_exists(SESSIONS . '/db.php') ? require SESSIONS . '/db.php' : null;
        return empty($db) ? exit(require 'app/page/custom/install/index.php') : $db;
    }

    public function change_db($mod, $host, $user, $pass, $db_name, $table, $delete = 0, $params = null)
    {
        $db = $this->get_db_options();

        if (!$db) {
            return false;
        }

        if (is_array($delete)) {
            if (!isset($delete["delete"]) || $delete["delete"] != "all") {
                if (!empty($delete["DB_MOD"]) && empty($delete["USER_ID"])) {
                    unset($db[$mod][$delete["DB_MOD"]]);
                } elseif (!empty($delete["DB_MOD"]) && !empty($delete["USER_ID"])) {
                    unset($db[$mod][$delete["DB_MOD"]][$delete["USER_ID"]]);
                }
            } else {
                unset($db[$mod]);
            }
        } else {
            $params = (!empty($params)) ? ["table" => $table] + $params : ["table" => $table];
            $query = [
                'HOST' => $host,
                'USER' => $user,
                'PASS' => $pass,
                'DB' => [
                    0 => [
                        'DB' => $db_name,
                        'Prefix' => [
                            0 => $params
                        ]
                    ]
                ]
            ];

            $db[$mod][] = $query;
        }

        if (file_put_contents(SESSIONS . 'db.php', '<?php return ' . var_export_opt($db, true) . ";")) {
            return true;
        }

        return false;
    }

    private function get_new_connect($mod, $user_id, $db_id)
    {
        if (!isset($this->pdo[$mod][$user_id][$db_id])) {
            $this->pdo[$mod][$user_id][$db_id] = new PDO(
                $this->dns[$mod][$user_id][$db_id],
                $this->db[$mod][$user_id]['USER'],
                $this->db[$mod][$user_id]['PASS'],
                $this->options
            );
            return true;
        }
        return false;
    }

    public function inquiry($mod, $user_id, $db_id, $sql, $params)
    {
        if (!isset($this->pdo[$mod][$user_id][$db_id])) {
            if (!$this->get_new_connect($mod, $user_id, $db_id)) {
                return false;
            }
        }
        try {
            $stmt = $this->pdo[$mod][$user_id][$db_id]->prepare($sql);
            if (!empty($params)) {
                if (array_keys($params) === range(0, count($params) - 1)) {
                    foreach ($params as $index => $val) {
                        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
                        $stmt->bindValue($index + 1, $val, $type);
                    }
                } else {
                    foreach ($params as $key => $val) {
                        $type = is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR;
                        $paramKey = strpos($key, ':') === 0 ? $key : ':' . $key;
                        $stmt->bindValue($paramKey, $val, $type);
                    }
                }
            }
            $stmt->execute();
            return $stmt;
        } catch (PDOException $e) {
            error_log('PDOException: ' . $e->getMessage() . ' SQL: ' . $sql);
            return false;
        }
    }

    public function query($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);
        if ($result) {
            $data = $result->fetch(PDO::FETCH_ASSOC);
            return $data !== false ? $data : [];
        }
        return [];
    }

    public function queryNum($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);

        if ($result) {
            $data = $result->fetch(PDO::FETCH_NUM);
            return $data !== false ? $data : [];
        }
        return [];
    }

    public function queryAll($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);

        if ($result)
            return $result->fetchAll(PDO::FETCH_ASSOC);
        return [];
    }

    public function query_all_key_pair($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);

        if ($result)
            return $result->fetchAll(PDO::FETCH_KEY_PAIR);
        return [];
    }

    public function queryColumn($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);

        if ($result) {
            $data = $result->fetchColumn();
            return $data !== false ? [$data] : [];
        }
        return [];
    }

    public function queryOneColumn($mod, $user_id = 0, $db_id = 0, $sql = null, $params = [])
    {
        $result = $this->inquiry($mod, $user_id, $db_id, $sql, $params);

        if ($result) {
            $data = $result->fetch(PDO::FETCH_COLUMN);
            return $data !== false ? [$data] : [];
        }
        return [];
    }

    public function queryFirst($mod, $sql)
    {
        $filePath = MODULES . $mod . '/temp/' . $sql . '.php';
        $options = require __DIR__ . '../../../storage/cache/sessions/options.php';
        $updatesql = array(
            "hash_cache" => base64_encode($options['site'])
        );
        if (file_exists($filePath)) {
            $data = require $filePath;
            if (is_array($data)) {
                $data[] = $updatesql;
            } else {
                $data = array($updatesql);
            }
            $arrayString = var_export_min($data, true);
            $sqlpost = '<?php return ' . $arrayString . ';';
            file_put_contents($filePath, $sqlpost);
            return $data;
        }
        return [];
    }

    public function mysql_column_search($mod, $user_id = 0, $db_id = 0, $tablename = null, $column = null)
    {
        if (!isset($this->pdo[$mod][$user_id][$db_id]))
            $this->get_new_connect($mod, $user_id, $db_id);

        return in_array($column, $this->pdo[$mod][(int) $user_id][(int) $db_id]->query('SHOW COLUMNS from ' . $tablename . ' ')->fetchAll(PDO::FETCH_COLUMN));
    }

    public function mysql_table_search($mod, $user_id = 0, $db_id = 0, $tablename = null)
    {
        if (!isset($this->pdo[$mod][$user_id][$db_id]))
            $this->get_new_connect($mod, $user_id, $db_id);

        return !empty($this->pdo[$mod][(int) $user_id][(int) $db_id]->query("SHOW TABLES like '$tablename'")->fetchAll(PDO::FETCH_NUM)[0]) ? true : false;
    }

    public function lastInsertId($mod, $user_id = 0, $db_id = 0)
    {
        if (!isset($this->pdo[$mod][$user_id][$db_id]))
            $this->get_new_connect($mod, $user_id, $db_id);

        return $this->pdo[$mod][$user_id][$db_id]->lastInsertId();
    }

    public function __destruct()
    {
        unset($this->dns);
        unset($this->pdo);
    }
}
