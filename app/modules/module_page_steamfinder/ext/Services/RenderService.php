<?php

namespace app\modules\module_page_steamfinder\ext\Services;

class RenderService
{
    protected $Translate, $General;

    public function __construct($Translate, $General)
    {
        $this->Translate = $Translate;
        $this->General = $General;
    }

    public function renderSteam($steam64, $steam32, $steam3, $url, $url_name, $banner, $avatar, $name, $lvl, $time_created, $last_logoff, $profile_state_text, $profile_state_class, $country, $status, $privacy_state, $community_ban, $vac_status, $game_bans, $economy_ban, $trade_status, $overall_status, $game_count, $total_playtime, $friends, $avg)
    {
        $steam = <<<HTML
            <div class="col-md-4">
                <div class="sf__card">
                    <div class="sf__copycards-wrapper">
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="{$steam64}">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">STEAMID64</div>
                                <div class="sf__copycards-text">{$steam64}</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="{$steam32}">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">STEAMID32</div>
                                <div class="sf__copycards-text">{$steam32}</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="[U:1:{$steam3}]">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">STEAMID3</div>
                                <div class="sf__copycards-text">[U:1:{$steam3}]</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="{$steam3}">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">Account id</div>
                                <div class="sf__copycards-text">{$steam3}</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="{$url}">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_originalLink')}</div>
                                <div class="sf__copycards-text">{$url}</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                        <div class="sf__copycards-card copy-btn" data-clipboard-text="{$url_name}">
                            <div class="sf__copycards-info">
                                <div class="sf__copycards-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_customLink')}</div>
                                <div class="sf__copycards-text">{$url_name}</div>
                            </div>
                            <div class="sf__copycards-icon">
                                <svg>
                                    <use href="/resources/img/sprite.svg#copy-list"></use>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="sf__card height-100 user">
                    <div class="sf__user-header">
                        <div class="sf__user-wrapper">
                            <div class="sf__user-details">
                                <img class="sf__user-avatar" src="{$avatar}" id="avatar" avatarid="{$steam64}" alt="">
                                <div class="sf__user-info">
                                    <h2>{$name}</h2>
                                    <div class="sf__user-subinfo">
                                        <div class="sf__user-subinfo-section">
                                            <span class="sf__user-subinfo-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_level')}</span>
                                            <span class="sf__user-subinfo-value">{$lvl}</span>
                                        </div>
                                        <div class="sf__user-subinfo-section">
                                            <span class="sf__user-subinfo-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_friends')}</span>
                                            <span class="sf__user-subinfo-value">{$friends}</span>
                                        </div>
                                        <div class="sf__user-subinfo-section">
                                            <span class="sf__user-subinfo-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_created')}</span>
                                            <span class="sf__user-subinfo-value">{$time_created}</span>
                                        </div>
                                        <div class="sf__user-subinfo-section">
                                            <span class="sf__user-subinfo-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_wasOnline')}</span>
                                            <span class="sf__user-subinfo-value">{$last_logoff}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="sf__user-comment {$profile_state_class}">
                                {$profile_state_text}
                            </div>
                        </div>
                        <div id="background" backgroundid="{$steam64}" class="sf__user-bg">
                            {$banner}
                        </div>
                        <div class="sf__user-links">
                            <button onclick="window.open('/profiles/{$steam64}/?search=1');">
                                <svg>
                                    <use href="/resources/img/sprite.svg#link"></use>
                                </svg>
                            </button>
                            <button onclick="window.open('https://steamcommunity.com/profiles/{$steam64}/');">
                                <svg>
                                    <use href="/resources/img/sprite.svg#steam"></use>
                                </svg>
                            </button>
                        </div>
                    </div>
                    <div class="sf__user-body">
                        <div class="sf__user-body-column">
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_profileType')}</div>
                                <div class="sf__user-body-block-value">{$privacy_state}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_status')}</div>
                                <div class="sf__user-body-block-value">{$status}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_country')}</div>
                                <div class="sf__user-body-block-value">{$country}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_totalGames')}</div>
                                <div class="sf__user-body-block-value">{$game_count}</div>
                            </div>
                        </div>
                        <div class="sf__user-body-column">
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_communityBanTitle')}</div>
                                <div class="sf__user-body-block-value">{$community_ban}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">VAC</div>
                                <div class="sf__user-body-block-value">{$vac_status}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_gameBanTitle')}</div>
                                <div class="sf__user-body-block-value">{$game_bans}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_averagePlayed')}</div>
                                <div class="sf__user-body-block-value">{$avg}</div>
                            </div>
                        </div>
                        <div class="sf__user-body-column">
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_banOnTradePlatformTitle')}</div>
                                <div class="sf__user-body-block-value">{$economy_ban}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_tradeBanTitle')}</div>
                                <div class="sf__user-body-block-value">{$trade_status}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_overallStatus')}</div>
                                <div class="sf__user-body-block-value">{$overall_status}</div>
                            </div>
                            <div class="sf__user-body-block">
                                <div class="sf__user-body-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_totalPlayed')}</div>
                                <div class="sf__user-body-block-value">{$total_playtime}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        HTML;

        return $steam;
    }

    public function renderFaceit($name, $elo, $lvl, $kd, $win, $hs, $total, $last, $streak)
    {
        if ($name) {
            $faceit = <<<HTML
                <div class="col-md-6">
                    <div class="sf__card">
                        <div class="sf__faceit-wrapper">
                            <div class="sf__faceit-header">
                                <img src="/app/modules/module_page_steamfinder/assets/img/faceit-logo.svg" alt="">
                                <div class="sf__faceit-rang">
                                    <span class="sf__faceit-elo">{$elo} ELO</span>
                                    <img src="/resources/img/faceit/{$lvl}.svg" alt="">
                                    <button onClick="window.open('https://www.faceit.com/ru/players/{$name}');">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#link"></use>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <hr>
                            <div class="sf__faceit-data">
                                <div class="sf__faceit-column">
                                    <div class="sf__faceit-block">
                                        <div class="sf__faceit-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_matches')}</div>
                                        <div class="sf__faceit-value">{$total}</div>
                                    </div>
                                    <div class="sf__faceit-block">
                                        <div class="sf__faceit-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_headshots')}</div>
                                        <div class="sf__faceit-value">{$hs}%</div>
                                    </div>
                                </div>
                                <div class="sf__faceit-column">
                                    <div class="sf__faceit-block">
                                        <div class="sf__faceit-title">K/D Ratio</div>
                                        <div class="sf__faceit-value">{$kd}</div>
                                    </div>
                                    <div class="sf__faceit-block">
                                        <div class="sf__faceit-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_winrate')}</div>
                                        <div class="sf__faceit-value">{$win}%</div>
                                    </div>
                                </div>
                                <div class="sf__faceit-column">
                                    <div class="sf__faceit-block">
                                        <div class="sf__faceit-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_killstreak')}</div>
                                        <div class="sf__faceit-value">{$streak}</div>
                                    </div>
                                    <div class="sf__faceit-block" data-tippy-content="{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_lastMatches')}" data-tippy-placement="top">
                                        <div class="sf__faceit-value sf__faceit-matches">
                                            {$last}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            HTML;
        } else {
            $faceit = <<<HTML
                <div class="col-md-6">
                    <div class="sf__card">
                        <div class="sf__faceit-wrapper">
                            <div class="sf__faceit-header">
                                <img src="/app/modules/module_page_steamfinder/assets/img/faceit-logo.svg" alt="">
                                <div class="sf__faceit-rang">
                                    <img src="/resources/img/faceit/none.svg" alt="">
                                </div>
                            </div>
                            <hr>
                            <div class="sf__faceit-data">
                                <div class="sf__faceit-data-empty">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_noData')}</div>
                            </div>
                        </div>
                    </div>
                </div>
            HTML;
        }

        return $faceit;
    }

    public function renderServers($serversStats)
    {
        $serversHTML = '';

        foreach ($serversStats as $server) {
            $serversHTML .= <<<HTML
                <div class="sf__activity-row">
                    <span class="sf__activity-server">{$server['server_name']}</span>
                    <span class="sf__activity-hours">{$server['playtime_hours']} {$this->Translate->get_translate_phrase('_Hour')}</span>
                </div>
            HTML;
        }

        if ($serversHTML) {
            $servers = <<<HTML
                <div class="col-md-6">
                    <div class="sf__card height-100">
                        <h3>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_gameActive')}</h3>
                        <div class="sf__activity-wrapper">
                            {$serversHTML}
                        </div>
                    </div>
                </div>
            HTML;
        } else {
            $servers = <<<HTML
                <div class="col-md-6">
                    <div class="sf__card height-100">
                        <h3>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_gameActive')}</h3>
                        <span class="sf__activity-empty">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_noData')}</span>
                    </div>
                </div>
            HTML;
        }

        return $servers;
    }

    public function renderCS2Stats($kills, $deaths, $kd, $mvp, $wins, $matchesPlayed, $shots, $hits, $accuracy, $totalPlayed)
    {
        $cs2 = <<<HTML
            <div class="col-md-12">
                <div class="sf__card">
                    <h3>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_gameStats')}</h3>
                    <div class="sf__stat-wrapper">
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_kills')}</div>
                            <div class="sf__stat-block-value">{$kills}</div>
                            <svg><use href="/resources/img/sprite.svg#headshot"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_deaths')}</div>
                            <div class="sf__stat-block-value">{$deaths}</div>
                            <svg><use href="/resources/img/sprite.svg#skull"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_kd')}</div>
                            <div class="sf__stat-block-value">{$kd}</div>
                            <svg><use href="/resources/img/sprite.svg#ratio"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_mvp')}</div>
                            <div class="sf__stat-block-value">{$mvp}</div>
                            <svg><use href="/resources/img/sprite.svg#star-fill"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_wins')}</div>
                            <div class="sf__stat-block-value">{$wins}</div>
                            <svg><use href="/resources/img/sprite.svg#trophy"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_matchesPlayed')}</div>
                            <div class="sf__stat-block-value">{$matchesPlayed}</div>
                            <svg><use href="/resources/img/sprite.svg#layers"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_shots')}</div>
                            <div class="sf__stat-block-value">{$shots}</div>
                            <svg><use href="/resources/img/sprite.svg#crosshair"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_hits')}</div>
                            <div class="sf__stat-block-value">{$hits}</div>
                            <svg><use href="/resources/img/sprite.svg#target"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_accuracy')}</div>
                            <div class="sf__stat-block-value">{$accuracy}%</div>
                            <svg><use href="/resources/img/sprite.svg#target"></use></svg>
                        </div>
                        <div class="sf__stat-block">
                            <div class="sf__stat-block-title">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_totalPlayed')}</div>
                            <div class="sf__stat-block-value">{$totalPlayed}</div>
                            <svg><use href="/resources/img/sprite.svg#time"></use></svg>
                        </div>
                    </div>
                </div>
            </div>
        HTML;

        return $cs2;
    }

    public function renderGames($top)
    {
        $game = '';
        foreach ($top as $key) {
            $hours = $key['playtime_hours'];
            $price = $key['price_rub'] == 0 ? $this->Translate->get_translate_module_phrase('module_page_steamfinder', '_free') : $key['price_rub'] . ' ' . $this->General->currency;
            $appid = $key['appid'];
            $name = $key['name'];
            $playtime_2weeks = $key['playtime_2weeks'];
            $game .= <<<HTML
                <tr>
                    <td class="sf__table-td"><div class="sf__table-img"><img src="https://cdn.steamstatic.com/steam/apps/{$appid}/capsule_184x69.jpg" alt=""></div></td>
                    <td>{$name}</td>
                    <!-- <td>{$price}</td> -->
                    <td class="sf__table-center">{$playtime_2weeks} {$this->Translate->get_translate_phrase('_Hour')}</td>
                    <td class="sf__table-center">{$hours} {$this->Translate->get_translate_phrase('_Hour')}</td>
                </tr>
            HTML;
        }
        $games = <<<HTML
            <div class="col-md-12">
                <div class="sf__card">
                    <h3>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_owned')}</h3>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_name')}</th>
                                    <!-- <th>{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_cost')}</th> -->
                                    <th class="sf__table-center">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_playedTwoWeeks')}</th>
                                    <th class="sf__table-center">{$this->Translate->get_translate_module_phrase('module_page_steamfinder', '_played')}</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$game}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        HTML;

        return $games;
    }
}
