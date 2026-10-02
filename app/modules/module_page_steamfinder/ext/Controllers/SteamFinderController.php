<?php

namespace app\modules\module_page_steamfinder\ext\Controllers;

use app\ext\Translate;
use app\modules\module_page_steamfinder\ext\Services\FaceitApiService;
use app\modules\module_page_steamfinder\ext\Services\SteamApiService;
use app\modules\module_page_steamfinder\ext\Services\RenderService;
use app\modules\module_page_steamfinder\ext\Services\AdditionalService;
use app\modules\module_page_steamfinder\ext\Services\DatabaseService;

class SteamFinderController
{
    protected $Db, $General, $SteamApiService, $FaceitApiService, $RenderService, $AdditionalService, $DatabaseService, $Modules, $Translate;

    public function __construct($Db, $General, $Translate, $Modules)
    {
        $this->General = $General;
        $this->Translate = $Translate;
        $this->DatabaseService = new DatabaseService($Db, $General);
        $this->SteamApiService = new SteamApiService($General, $Translate);
        $this->FaceitApiService = new FaceitApiService($General, $Translate, $Modules);
        $this->RenderService = new RenderService($Translate, $General);
        $this->AdditionalService = new AdditionalService($Translate);
    }

    public function dataCollection($input)
    {
        $steamInfo = $this->SteamApiService->getAllSteamFormats($input);

        if ($steamInfo['steam64']) {
            $steam64 = $steamInfo['steam64'];
            $steamInfoProfile = $this->SteamApiService->getSteamProfileInfo($steam64);
            $profile_url_username = action_text_clear($steamInfo['profile_url_username']);
            $name = action_text_clear($steamInfoProfile['nickname']);
            $time_created = $this->AdditionalService->getCreatedTime($steamInfoProfile['time_created']);
            $last_logoff = $this->AdditionalService->getLastLogOff($steamInfoProfile['last_logoff']);
            $profile_state_text = $this->AdditionalService->getProfileStateText($steamInfoProfile['profile_state']);
            $profile_state_class = $steamInfoProfile['profile_state'] == 1 ? 'good' : 'bad';
            $status = $this->AdditionalService->getUserStatus($steamInfoProfile['status']);
            $privacy_state = $this->AdditionalService->getPrivacyState($steamInfoProfile['privacy_state']);
            $lvl = $this->AdditionalService->checkUnknown($steamInfoProfile['steam_level']);
            $friends = $this->AdditionalService->checkUnknown($steamInfoProfile['friends_count']);
            $country = $steamInfoProfile['country'] ? '<img src="/' . IMG . 'icons/custom/flags/' . strtolower($steamInfoProfile['country']) . '.svg">' . $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_' . $steamInfoProfile['country']) . '' : 'Неизвестно';
            $steamInfoBan = $this->SteamApiService->getSteamBanInfo($steam64);
            $community_ban = $this->AdditionalService->getCommunityBan($steamInfoBan['community_ban']);
            $vac_status = $this->AdditionalService->getVacBan($steamInfoBan['vac_status'], $steamInfoBan['vac_banned_games_count'], $steamInfoBan['last_vac_ban_days_ago']);
            $game_bans = $this->AdditionalService->getGameBan($steamInfoBan['game_bans']);
            $economy_ban = $this->AdditionalService->getEconomyBanStatus($steamInfoBan['economy_ban']);
            $trade_status = $this->AdditionalService->getTradeStatus($steamInfoBan['trade_status']);
            $overall_status = $this->AdditionalService->getOverallBanStatus($steamInfoBan['overall_status']);
            $steamInfoGame = $this->SteamApiService->getSteamGamesInfo($steam64);
            $game_count = $this->AdditionalService->countConverter($steamInfoGame['game_count']);
            $total_playtime = $this->AdditionalService->hoursConverter($steamInfoGame['total_playtime_hours']);
            $avg = $this->AdditionalService->hoursConverter($steamInfoGame['average_playtime_hours']);
            $this->General->arr_general['theme'] == 'neo' ? $banner = '' : $banner = $this->General->getBackground($steam64);
            $steamRender = $this->RenderService->renderSteam(
                $steam64,
                $steamInfo['steam32'],
                $steamInfo['steam3'],
                $steamInfo['profile_url'],
                $profile_url_username,
                $banner,
                $this->General->getAvatar($steam64, 3),
                $name,
                $lvl,
                $time_created,
                $last_logoff,
                $profile_state_text,
                $profile_state_class,
                $country,
                $status,
                $privacy_state,
                $community_ban,
                $vac_status,
                $game_bans,
                $economy_ban,
                $trade_status,
                $overall_status,
                $game_count,
                $total_playtime,
                $friends,
                $avg
            );

            $faceitStats = $this->FaceitApiService->getFaceitStats($steam64);
            $last_5_matches = $this->AdditionalService->formatLastMatches($faceitStats['last_5_matches']);
            $faceitRender = $this->RenderService->renderFaceit($faceitStats['nickname'], $faceitStats['elo'], $faceitStats['faceit_level'], $faceitStats['kd_ratio'], $faceitStats['win_rate'], $faceitStats['headshot_percent'], $faceitStats['total_matches'], $last_5_matches, $faceitStats['max_win_streak']);

            $serversStats = $this->DatabaseService->getInfoServers($steamInfo['steam32'], $steam64);
            $serversRender = $this->RenderService->renderServers($serversStats);

            $cs2_playtime = $this->AdditionalService->hoursConverter($steamInfoGame['cs2_playtime_hours']);
            $cs2Stats = $this->SteamApiService->getCs2Stats($steam64);
            $cs2Render = $this->RenderService->renderCS2Stats($cs2Stats['kills'], $cs2Stats['deaths'], $cs2Stats['kd'], $cs2Stats['mvps'], $cs2Stats['wins'], $cs2Stats['matches_played'], $cs2Stats['shots_fired'], $cs2Stats['shots_hit'], $cs2Stats['accuracy_percent'], $cs2_playtime);

            $gameRender = $this->RenderService->renderGames($steamInfoGame['top_games']);

            $checkAvatar = $this->General->checkAvatar($steam64);
            $this->SteamApiService->getCs2Stats($steam64);

            return ['status' => 'success', 'steam' => $steamRender, 'faceit' => $faceitRender, 'servers' => $serversRender, 'cs2stats' => $cs2Render, 'steamid' => $steam64, 'checkAvatar' => $checkAvatar, 'games' => $gameRender];
        } else {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_invalidSteamID')];
        }
    }
}
