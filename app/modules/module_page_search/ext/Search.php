<?php

namespace app\modules\module_page_search\ext;

class Search
{
    protected $limit = 15;
    protected $min_value = 0;

    protected $search_data;
    protected $search_serialize;
    protected $Db;
    protected $General;

    public function __construct($Db, $General)
    {
        $search = $_POST['search'] ?? '';
        $search = htmlspecialchars(trim($search), ENT_QUOTES, 'UTF-8');

        if ($search !== '') {
            $this->Db = $Db;
            $this->General = $General;
            $this->search_data = $search;

            if (!$this->search_serialize = $this->steamConvert()) {
                self::returnJsonError("Неизвестный тип поиска!");
            }
        } else {
            self::returnJsonError("Неизвестный тип поиска!");
        }
    }

    public function returnJson()
    {
        $players = $this->findPlayerInDb();

        if (!empty($players)) {
            foreach ($players as $key => $val) {
                $steam64 = con_steam64($val["steam"]);
                $players[$key]["avatar"] = $this->General->getAvatar($steam64, 1);
                $players[$key]["name"] = action_text_clear($val["name"]);
                $players[$key]["steam64"] = $steam64;
            }
        }

        self::returnJsonSuccess(["players" => $players]);
    }

    protected function findPlayerInDb()
    {
        $result = [];

        $searchTerm = $this->getSearchResult();
        $searchType = $this->getSearchType();

        foreach ($this->Db->db_data['LevelsRanks'] as $val) {
            if ($searchType === "name") {
                $query = "SELECT `steam`, `name` FROM `{$val['Table']}` WHERE `value` >= :minValue AND `name` LIKE :name LIMIT :limit";
                $params = [
                    'minValue' => $this->min_value,
                    'name' => "%$searchTerm%",
                    'limit' => $this->limit
                ];
            } else {
                $steamParams = $this->steam0to1($searchTerm);
                $query = "SELECT `steam`, `name` FROM `{$val['Table']}` WHERE `value` >= :minValue AND (`steam` = :steam1 OR `steam` = :steam2) LIMIT :limit";
                $params = [
                    'minValue' => $this->min_value,
                    'steam1' => $steamParams['steam1'],
                    'steam2' => $steamParams['steam2'],
                    'limit' => $this->limit
                ];
            }

            $result = array_merge(
                $result,
                $this->Db->queryAll('LevelsRanks', $val['USER_ID'], $val['DB_num'], $query, $params)
            );
        }

        return $this->unique_multidim_array($result, "steam");
    }

    protected function steamConvert()
    {
        $val = $this->search_data;

        if (empty($val)) {
            return false;
        }

        try {
            if (strpos($val, 'https://steamcommunity.com/') !== false) {
                $steam = SteamidConverter::SetFromURL($val, function ($vanity, $type) {

                    $apiKey = $this->General->arr_general["web_key"];
                    if (!$apiKey) {
                        return null;
                    }

                    $url = "https://api.steampowered.com/ISteamUser/ResolveVanityURL/v0001/?key={$apiKey}&vanityurl={$vanity}";
                    $response = file_get_contents($url);
                    $data = json_decode($response, true);

                    if (isset($data['response']['steamid'])) {
                        return $data['response']['steamid'];
                    }

                    return null;
                });
                return [
                    "steam" => $steam->RenderSteam2()
                ];
            }

            return [
                "steam" => (string) (new SteamidConverter($val))->RenderSteam2()
            ];
        } catch (\Exception $e) {
            return [
                "name" => htmlentities($val)
            ];
        }
    }

    protected function getSearchType()
    {
        return isset($this->search_serialize["steam"]) ? "steam" : "name";
    }

    public function getSearchResult()
    {
        return $this->search_serialize[$this->getSearchType()];
    }

    protected function steam0to1($steam)
    {
        $steam1 = str_replace("STEAM_0", "STEAM_1", $steam);
        return ['steam1' => $steam, 'steam2' => $steam1];
    }

    protected function unique_multidim_array($array, $key)
    {
        $temp_array = [];
        $key_array = [];

        foreach ($array as $val) {
            if (!in_array($val[$key], $key_array)) {
                $key_array[] = $val[$key];
                $temp_array[] = $val;
            }
        }

        return $temp_array;
    }

    protected static function returnJsonSuccess($data)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(["result" => $data], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    protected static function returnJsonError($message)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode(["error" => $message], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }
}
