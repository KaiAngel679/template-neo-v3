<div class="col-md-9">
	<div class="card">
		<div class="card-header">
			<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Information_player'); ?></div>
		</div>
		<div class="card-container">
			<div class="general_info">
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 36 36" xml:space="preserve" class="">
							<g>
								<g fill-rule="evenodd">
									<path d="M22.85 9.72C21.56 8.43 19.83 7.71 18 7.71s-3.56.71-4.85 2.01c-2.67 2.67-2.67 7.02 0 9.7 2.67 2.67 7.02 2.67 9.7 0 2.67-2.67 2.67-7.02 0-9.7z" fill="var(--text-default)"></path>
									<path d="M18 2C11.06 2 5.43 7.63 5.43 14.57 5.43 27.48 18 34 18 34s12.57-6.52 12.57-19.43C30.57 7.63 24.94 2 18 2zm0 21.71c-2.34 0-4.68-.89-6.46-2.67-3.56-3.56-3.56-9.37 0-12.93 1.73-1.73 4.02-2.68 6.47-2.68s4.74.95 6.46 2.68c3.57 3.56 3.57 9.37 0 12.93A9.113 9.113 0 0 1 18 23.71z" fill="var(--text-default)"></path>
								</g>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_location'); ?>
					</div>
					<div class="country_container">
						<div class="country_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Country'); ?></div>
							<span class="content_column">
								<img class="size_image" src="<?= $General->arr_general['site'] ?>storage/cache/img/global/flags/<?= ($Player->get_db_plugin_module_geoip()['country']) ?>@2x.svg" onerror="this.src='<?= $General->arr_general['site'] ?>storage/cache/img/global/flags/World@2x.svg'" alt="" title="">
								<?php empty($Player->get_db_plugin_module_geoip()) ? print $Translate->get_translate_module_phrase('module_page_profiles', '_Unknown') : print $Player->get_db_plugin_module_geoip()['country'] ?>
							</span>
						</div>
						<?php $msAccess = $Db->query('Core', 0, 0, "SELECT `add_ban_access` FROM `lvl_web_managersystem_access` WHERE `steamid_access` = :steamid_access", ['steamid_access' => $_SESSION['steamid64']]); ?>
						<div class="country_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_City'); ?></div>
							<?php if (isset($_SESSION['user_admin']) || (isset($msAccess['add_ban_access']) && $msAccess['add_ban_access'] == 1)): ?>
								<span class="content_column"><?= empty($Player->get_db_plugin_module_geoip()['city']) ? $Translate->get_translate_module_phrase('module_page_profiles', '_Unknown') : '<span class="hide-ip">' . $Player->get_db_plugin_module_geoip()['city'] . '</span>' ?></span>
							<?php else: ?>
								<span class="content_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_hidden'); ?></span>
							<?php endif; ?>
						</div>
						<div class="country_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_IP_address'); ?></div>
							<?php if (isset($_SESSION['user_admin']) || (isset($msAccess['add_ban_access']) && $msAccess['add_ban_access'] == 1)): ?>
								<span class="content_column">
									<?= empty($Player->get_db_plugin_module_geoip()['clientip']) ? $Translate->get_translate_module_phrase('module_page_profiles', '_Unknown') : '<span class="hide-ip">' .  $Player->get_db_plugin_module_geoip()['clientip'] . '</span>' ?>
								</span>
							<?php else: ?>
								<span class="content_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_hidden'); ?></span>
							<?php endif; ?>
						</div>
					</div>
					<div class="title_head">
						<svg height="1520" viewBox="29.3 101.1 451.7 357.9">
							<path d="m481 104.8c0-1.8-1.9-3.7-1.9-3.7-1.8 0-1.8 0-3.7 1.9-37.5 58.1-76.8 116.2-114.3 176.2h-326.2c-3.7 0-5.6 5.6-1.8 7.5 134.9 50.5 331.7 127.3 440.4 170.4 3.7 1.9 7.5-1.9 7.5-3.7z" fill="#fd5a00" />
							<path d="m481 104.8c0-1.8-1.9-3.7-1.9-3.7-1.8 0-1.8 0-3.7 1.9-37.5 58.1-76.8 116.2-114.3 176.2l119.9 1.23z" fill="#ff690a" />
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_faceitstats'); ?>
					</div>
					<div class="faceit_container">
						<?php $General->get_js_relevance_faceit($Player->get_steam_64()) ?>
						<div class="faceit_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_NickName'); ?></div>
							<span class="content_column" id="faceit" faceit_nickname="<?= $Player->get_steam_64() ?>">
								<?= $General->getFaceit($Player->get_steam_64(), 'nickname') ?>
							</span>
						</div>
						<div class="faceit_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Indicators'); ?></div>
							<span class="content_column" id="faceit" faceit_elo="<?= $Player->get_steam_64() ?>"><?= $General->getFaceit($Player->get_steam_64(), 'elo') ?></span>
						</div>
						<div class="faceit_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_FaceitLevel'); ?></div>
							<span class="content_column">
								<img id="faceit" faceit_level_img="<?= $Player->get_steam_64() ?>" src="<?= $General->getFaceit($Player->get_steam_64(), 'level_img') ?>" alt="" title="">
							</span>
						</div>
					</div>
					<div class="title_head">
						<svg viewBox="0 0 65 65" fill="#fff">
							<use xlink:href="#B" x=".5" y=".5"></use>
							<defs>
								<linearGradient id="A" x2="50%" x1="50%" y2="100%" y1="0%">
									<stop stop-color="#111d2e" offset="0%"></stop>
									<stop stop-color="#051839" offset="21.2%"></stop>
									<stop stop-color="#0a1b48" offset="40.7%"></stop>
									<stop stop-color="#132e62" offset="58.1%"></stop>
									<stop stop-color="#144b7e" offset="73.8%"></stop>
									<stop stop-color="#136497" offset="87.3%"></stop>
									<stop stop-color="#1387b8" offset="100%"></stop>
								</linearGradient>
							</defs>
							<symbol id="B">
								<g>
									<path d="M1.305 41.202C5.259 54.386 17.488 64 31.959 64c17.673 0 32-14.327 32-32s-14.327-32-32-32C15.001 0 1.124 13.193.028 29.874c2.074 3.477 2.879 5.628 1.275 11.328z" fill="url(#A)"></path>
									<path d="M30.31 23.985l.003.158-7.83 11.375c-1.268-.058-2.54.165-3.748.662a8.14 8.14 0 0 0-1.498.8L.042 29.893s-.398 6.546 1.26 11.424l12.156 5.016c.6 2.728 2.48 5.12 5.242 6.27a8.88 8.88 0 0 0 11.603-4.782 8.89 8.89 0 0 0 .684-3.656L42.18 36.16l.275.005c6.705 0 12.155-5.466 12.155-12.18s-5.44-12.16-12.155-12.174c-6.702 0-12.155 5.46-12.155 12.174zm-1.88 23.05c-1.454 3.5-5.466 5.147-8.953 3.694a6.84 6.84 0 0 1-3.524-3.362l3.957 1.64a5.04 5.04 0 0 0 6.591-2.719 5.05 5.05 0 0 0-2.715-6.601l-4.1-1.695c1.578-.6 3.372-.62 5.05.077 1.7.703 3 2.027 3.696 3.72s.692 3.56-.01 5.246M42.466 32.1a8.12 8.12 0 0 1-8.098-8.113 8.12 8.12 0 0 1 8.098-8.111 8.12 8.12 0 0 1 8.1 8.111 8.12 8.12 0 0 1-8.1 8.113m-6.068-8.126a6.09 6.09 0 0 1 6.08-6.095c3.355 0 6.084 2.73 6.084 6.095a6.09 6.09 0 0 1-6.084 6.093 6.09 6.09 0 0 1-6.081-6.093z"></path>
								</g>
							</symbol>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_steamid'); ?>
					</div>
					<div class="steam_container">
						<div class="steam_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_steamID32'); ?></div>
							<span class="content_column copy-btn" data-clipboard-text="<?= $Player->get_steam_32() ?>" style="cursor: pointer">
								<svg class="size_image_copy" viewBox="0 0 512 512">
									<path d="M224 0c-35.3 0-64 28.7-64 64V288c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64H224zM64 160c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H288c35.3 0 64-28.7 64-64V384H288v64H64V224h64V160H64z" />
								</svg>
								<?= $Player->get_steam_32() ?>
							</span>
						</div>
						<div class="steam_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_steamID64'); ?></div>
							<span class="content_column copy-btn" data-clipboard-text="<?= $Player->get_steam_64() ?>" style="cursor: pointer">
								<svg class="size_image_copy" viewBox="0 0 512 512">
									<path d="M224 0c-35.3 0-64 28.7-64 64V288c0 35.3 28.7 64 64 64H448c35.3 0 64-28.7 64-64V64c0-35.3-28.7-64-64-64H224zM64 160c-35.3 0-64 28.7-64 64V448c0 35.3 28.7 64 64 64H288c35.3 0 64-28.7 64-64V384H288v64H64V224h64V160H64z" />
								</svg>
								<?= $Player->get_steam_64() ?>
							</span>
						</div>
					</div>
				</div>
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 64 64" xml:space="preserve" fill-rule="evenodd">
							<g>
								<path d="M56.315 31.037C55.424 31 54.484 31 53.62 31a4.5 4.5 0 0 0-4.448 3.821c-1.307 8.292-8.501 14.62-17.172 14.62-9.626 0-17.441-7.815-17.441-17.441 0-8.673 6.331-15.868 14.623-17.214a4.456 4.456 0 0 0 3.776-4.389C33 9.542 33 8.601 33 7.711a4.5 4.5 0 0 0-5.081-4.464l-.013.002C13.831 5.274 3 17.372 3 32c0 16.006 12.994 29 29 29 14.629 0 26.727-10.832 28.714-24.913l.002-.011a4.464 4.464 0 0 0-4.401-5.039z" fill="var(--text-default)"></path>
								<path d="M35.109 7.004v14.858a7 7 0 0 0 7 7c4.672 0 10.972 0 14.718-.031a3.968 3.968 0 0 0 3.928-4.481l-.001-.01C59.211 13.391 50.585 4.704 39.67 3.043l-.014-.002a4 4 0 0 0-4.547 3.963z" fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Stats'); ?>
					</div>
					<div class="stats_container">
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Rank'); ?></div>
							<span class="content_column"><?= $Translate->get_translate_phrase($Player->get_rank(), 'ranks_' . $Player->found[$Player->server_group]['ranks_pack']) ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Exp'); ?></div>
							<span class="content_column"><?= $Player->get_value() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Top_players'); ?></div>
							<span class="content_column"><?= $Player->get_top_position() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Time_Played'); ?></div>
							<span class="content_column"><?= $Player->get_playtime() ?> <?= $Translate->get_translate_module_phrase('module_page_profiles', '_hours'); ?></span>
						</div>
					</div>
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 48 48" xml:space="preserve" class="">
							<g>
								<path d="M3 17h6a1 1 0 0 0 1-1c.133-1.613-.282-3.263-1.619-4.195C9.9 9.954 8.417 6.922 6 7c-2.416-.076-3.901 2.953-2.381 4.805A3.982 3.982 0 0 0 2 15v1a1 1 0 0 0 1 1zM3 29h6a1 1 0 0 0 1-1c.133-1.613-.282-3.263-1.619-4.195C9.9 21.954 8.417 18.922 6 19c-2.416-.076-3.901 2.953-2.381 4.805A3.982 3.982 0 0 0 2 27v1a1 1 0 0 0 1 1zM3 41h6a1 1 0 0 0 1-1c.133-1.613-.282-3.263-1.619-4.195C9.9 33.954 8.417 30.922 6 31c-2.416-.076-3.901 2.953-2.381 4.805A3.982 3.982 0 0 0 2 39v1a1 1 0 0 0 1 1zM39 36c0-2.206-1.794-4-4-4H14c-1.654 0-3 1.346-3 3v2c0 1.654 1.346 3 3 3h21c2.206 0 4-1.794 4-4zM21 20h-7c-1.654 0-3 1.346-3 3v2c0 1.654 1.346 3 3 3h7c5.284-.167 5.287-7.832 0-8zM42 8H14c-1.654 0-3 1.346-3 3v2c0 1.654 1.346 3 3 3h28c5.277-.164 5.293-7.831 0-8z" fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_Effectiveness'); ?>
					</div>
					<div class="stats_container">
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Wins'); ?></div>
							<span class="content_column"><?= $Player->get_percent_win() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Rounds'); ?></div>
							<span class="content_column"><?= ($Player->get_round_lose() + $Player->get_round_win()) ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Kill_count'); ?></div>
							<span class="content_column"><?= $Player->get_kills() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Deaths'); ?></div>
							<span class="content_column"><?= $Player->get_deaths() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Ratio_KD'); ?></div>
							<span class="content_column"><?= $Player->get_kd() ?></span>
						</div>
					</div>
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 512 512" xml:space="preserve" class="">
							<g>
								<path d="M256 120.5c-74.715 0-135.5 60.785-135.5 135.5S181.285 391.5 256 391.5 391.5 330.715 391.5 256 330.715 120.5 256 120.5zm48.341 119.22-49.333 53a15.001 15.001 0 0 1-21.586.386l-25.389-25.389c-5.858-5.857-5.858-15.355 0-21.213 5.858-5.857 15.356-5.857 21.213 0l14.396 14.396 38.741-41.62c5.645-6.064 15.136-6.404 21.2-.76 6.062 5.645 6.402 15.136.758 21.2z" fill="var(--text-default)"></path>
								<path d="M497 241h-19.939c-3.556-53.537-26.093-103.381-64.387-141.675C374.381 61.032 324.537 38.494 271 34.939V15c0-8.284-6.716-15-15-15s-15 6.716-15 15v19.939c-53.537 3.556-103.381 26.094-141.675 64.387C61.032 137.619 38.494 187.463 34.939 241H15c-8.284 0-15 6.716-15 15s6.716 15 15 15h19.939c3.556 53.537 26.093 103.381 64.387 141.675 38.294 38.293 88.138 60.831 141.675 64.386V497c0 8.284 6.716 15 15 15s15-6.716 15-15v-19.939c53.537-3.556 103.381-26.094 141.675-64.386 38.293-38.294 60.831-88.138 64.387-141.675H497c8.284 0 15-6.716 15-15s-6.716-15-15-15zM271 446.986v-18.844c0-8.284-6.716-15-15-15s-15 6.716-15 15v18.844C147.302 439.695 72.305 364.698 65.014 271h18.843c8.284 0 15-6.716 15-15s-6.716-15-15-15H65.014C72.305 147.302 147.302 72.305 241 65.014v18.844c0 8.284 6.716 15 15 15s15-6.716 15-15V65.014C364.698 72.305 439.695 147.302 446.986 241h-18.843c-8.284 0-15 6.716-15 15s6.716 15 15 15h18.843C439.695 364.698 364.698 439.695 271 446.986z" fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_Mettle'); ?>
					</div>
					<div class="stats_container">
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Assists'); ?></div>
							<span class="content_column"><?= $Player->get_assists() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_phrase('_Headshot') ?></div>
							<span class="content_column"><?= $Player->get_percent_headshots() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Shoots'); ?></div>
							<span class="content_column"><?= $Player->get_shoots() ?></span>
						</div>
						<div class="stats_column">
							<div class="title_column"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Hits'); ?></div>
							<span class="content_column"><?= $Player->get_hits() ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>