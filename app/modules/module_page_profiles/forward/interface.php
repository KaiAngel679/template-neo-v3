<script>
	var info = <?= json_encode(array("name" => empty($General->checkName($Player->get_steam_64())) ? $Player->get_name() : $General->checkName($Player->get_steam_64()), "lastconnect" => $Player->get_lastconnect())) ?>;
</script>
<div class="row">
	<div class="col-md-3">
		<div class="adaptive-select-wrapper">
			<ul class="adaptive-select__dropdown-list" id="option-server-select">
				<?php for ($b = 0, $_c = sizeof($Player->found); $b < $_c; $b++) {
					if (!empty($Player->found_fix[$b])) { ?>
						<li>
							<label class="adaptive-select__label" for="for_server_<?= $b ?>">
								<div class="adaptive-select__label-text"><?= $Player->found_fix[$b]['name_servers'] ?></div>
								<input class="hide-input" id="for_server_<?= $b ?>" name="server" type="radio" onclick="window.location.href=this.value" value="<?= $General->arr_general['site'] ?>profiles/<?= con_steam64($Player->get_steam_64()) ?>/<?= $Player->found_fix[$b]['server_group'] ?>" <?= ($Player->found_fix[$b]['server_group']) == ($Player->found[$Player->server_group]['server_group']) ? 'checked' : '' ?>>
							</label>
						</li>
				<?php }
				} ?>
			</ul>
			<div class="adaptive-select" open-select="option-server-select">
				<span class="adaptive-select__span_text">-</span>
				<span class="margin-left-auto adaptive-select__arrow">
					<svg>
						<use href="/resources/img/sprite.svg#chevron-down"></use>
					</svg>
				</span>
			</div>
		</div>
		<div class="card">
			<div class="profile_user_card">
				<div class="user_back">
					<div id="background" backgroundid="<?= $Player->get_steam_64() ?>">
						<?= $General->getBackground($Player->get_steam_64()) ?>
					</div>
					<div class="header_user_info">
						<?php if (isset($_SESSION["steamid64"]) && ($Player->get_steam_64() == $_SESSION['steamid64'])): ?>
							<a class="user_settings" data-openmodal="profileSettings" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Settings'); ?>" data-tippy-placement="left">
								<svg>
									<use href="/resources/img/sprite.svg#gear"></use>
								</svg>
							</a>
						<?php endif; ?>
						<div class="user_socials">
							<a class="social__button-profile" id="faceit" faceit_url="<?= $Player->get_steam_64() ?>" href="<?= $General->getFaceit($Player->get_steam_64(), 'url') ?>" target="_blank" <?= empty($General->getFaceit($Player->get_steam_64(), 'url')) ? 'style="display: none;"' : '' ?>>
								<svg>
									<use href="/resources/img/sprite.svg#faceit-logo"></use>
								</svg>
							</a>
							<a class="social__button-profile" href="//steamcommunity.com/profiles/<?= $Player->get_steam_64() ?>/" target="_blank">
								<svg>
									<use href="/resources/img/sprite.svg#steam"></use>
								</svg>
							</a>
							<?php if (!empty($Info['vk'])): ?>
								<a class="social__button-profile vkontakte" href="//vk.com/<?= action_text_clear($Info['vk']) ?>" target="_blank">
									<svg>
										<use href="/resources/img/sprite.svg#vk"></use>
									</svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($Info['tg'])): ?>
								<a class="social__button-profile telegram" href="//t.me/<?= action_text_clear($Info['tg']) ?>" target="_blank">
									<svg>
										<use href="/resources/img/sprite.svg#tg"></use>
									</svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($Info['twitch'])): ?>
								<a class="social__button-profile" href="//twitch.com/<?= action_text_clear($Info['twitch']) ?>" target="_blank">
									<svg id="Layer_1" x="0px" y="0px" viewBox="0 0 2400 2800" xml:space="preserve">
										<g>
											<polygon fill="#fff"
												points="2200,1300 1800,1700 1400,1700 1050,2050 1050,1700 600,1700 600,200 2200,200" />
											<g>
												<g id="Layer_1-2">
													<path fill="#9146FF" d="M500,0L0,500v1800h600v500l500-500h400l900-900V0H500z M2200,1300l-400,400h-400l-350,350v-350H600V200h1600
													V1300z" />
													<rect x="1700" y="550" fill="#9146FF" width="200" height="600" />
													<rect x="1150" y="550" fill="#9146FF" width="200" height="600" />
												</g>
											</g>
										</g>
									</svg>
								</a>
							<?php endif; ?>
							<?php if (!empty($Info['discord'])): ?>
								<div data-clipboard-text="<?= action_text_clear($Info['discord']) ?>" class="social__button-profile copy-btn discord">
									<svg>
										<use href="/resources/img/sprite.svg#ds"></use>
									</svg>
								</div>
							<?php endif; ?>
						</div>
						<?php $General->get_js_relevance_avatar($Player->get_steam_64()) ?>
						<img class="lazy" data-src="<?= $General->getAvatar($Player->get_steam_64(), 3) ?>" id="avatar" avatarid="<?= $Player->get_steam_64() ?>" alt="" title="">
						<div class="online_pos">
							<div class="user_online_status" style="<?php if ($General->checkOnline($Player->get_steam_64()) == 1) {
																												echo 'display: block;';
																											} ?>"></div>
						</div>
					</div>
				</div>
				<div class="user_sec_block">
					<?php if (isset($_SESSION["steamid64"]) && ($_SESSION["user_admin"] || ($Player->get_steam_64() == $_SESSION['steamid64']))): ?>
						<div onclick="location.href='<?= $General->arr_general['site'] ?>pay/'" class="user_balance"
							data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_balance'); ?>"
							data-tippy-placement="bottom">
							<div class="balance_info">
								<span class="balance_count">
									<?php if (!empty($Player->Db->db_data['Core'])): ?>
										<?= $Player->get_balance() . ' ' . $General->currency; ?>
									<?php endif; ?>
								</span>
							</div>
							<svg viewBox="0 0 512 512">
								<path
									d="M232 344V280H168C154.7 280 144 269.3 144 256C144 242.7 154.7 232 168 232H232V168C232 154.7 242.7 144 256 144C269.3 144 280 154.7 280 168V232H344C357.3 232 368 242.7 368 256C368 269.3 357.3 280 344 280H280V344C280 357.3 269.3 368 256 368C242.7 368 232 357.3 232 344zM512 256C512 397.4 397.4 512 256 512C114.6 512 0 397.4 0 256C0 114.6 114.6 0 256 0C397.4 0 512 114.6 512 256zM256 48C141.1 48 48 141.1 48 256C48 370.9 141.1 464 256 464C370.9 464 464 370.9 464 256C464 141.1 370.9 48 256 48z">
								</path>
							</svg>
						</div>
					<?php endif; ?>
					<?= getRankImage($Player->get_rank() ?: 0, $Player->get_value() ?: 0, $Player->found[$Player->server_group]['ranks_pack']) ?>
				</div>
				<div class="user_third_block">
					<div class="user__head-detalis">
						<div class="user_nickname">
							<?= empty($General->checkName($Player->get_steam_64())) ? action_text_clear($Player->get_name()) : action_text_clear($General->checkName($Player->get_steam_64())) ?>
						</div>
						<a id="connect_link"><span class="user_status_server" id="online_status"><?= $Player->get_lastconnect() ?></span></a>
					</div>
					<hr>
					<div class="user_status">
						<span
							class="status_title"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_status'); ?></span>
						<span class="status_content"><?php if (empty($Info['status'])) {
																						echo $Translate->get_translate_module_phrase('module_page_profiles', '_Not_specified');
																					} else {
																						echo action_text_clear($Info['status']);
																					} ?></span>
					</div>
					<hr>
					<div class="user_roles">
						<?php if (empty($Player->get_db_Vips()) && (empty($Admins))): ?>
							<div class="user_badge badge_player">
								<span class="badge_player_circle"></span>
								<?= $Translate->get_translate_module_phrase('module_page_profiles', '_player'); ?>
							</div>
						<?php endif; ?>
						<?php if (!empty($Admins)): ?>
							<div class="user_badge badge_admin">
								<span class="badge_admin_circle"></span>
								<?php if (!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['IksAdminNew']) || !empty($Db->db_data['AdminSystem']) && in_array($Player->lws['server_sb'][0], ['IksAdmin', 'IksAdminNew', 'AdminSystem'])) {
									$groupFound = false;
									foreach ($Groups as $key) {
										if ($Admins['group_id'] == -1 || $Admins['group_id'] == 0) {
											echo $Translate->get_translate_module_phrase('module_page_profiles', '_No_Group');
											$groupFound = true;
											break;
										} else {
											if ($Admins['group_id'] == $key['id']) {
												echo $key['name'];
												$groupFound = true;
												break;
											}
										}
									}
									if (!$groupFound) {
										echo $Translate->get_translate_module_phrase('module_page_profiles', '_No_Group');
									}
								} elseif (!empty($Db->db_data['SourceBans']) && $Player->lws['server_sb'][0] == 'SourceBans') {
									echo $Admins['srv_group'];
								} ?>
							</div>
						<?php endif; ?>
						<?php if ($Player->get_db_Vips()): ?>
							<div class="user_badge badge_vip">
								<span class="badge_vip_circle"></span>
								<?= $Vips['group'] ?>
							</div>
						<?php endif; ?>
						<?php if ($Player->get_top_position() < 4): ?>
							<div class="user_badge <?= 'badge_top-' . $Player->get_top_position(); ?>">
								<span class="<?= 'badge_top_circle-' . $Player->get_top_position(); ?>"></span>
								TOP <?= $Player->get_top_position() ?>
							</div>
						<?php endif; ?>
						<?php if (!empty($Player->Db->db_data['IksAdmin'])):
							$ban_check = $Db->query('IksAdmin', $Db->db_data['IksAdmin'][0]['USER_ID'], $Db->db_data['IksAdmin'][0]['DB_num'], "SELECT `created`, `sid`, `end`, `time`, `Unbanned` FROM `iks_bans` WHERE `sid` LIKE '%" . $Player->get_steam_64() . "%' order by `created` desc limit 1");
							!empty($ban_check) && ((empty($ban_check['time']) || $ban_check['end'] >= time()) && empty($ban_check['Unbanned'])) && (print '<div class="user_badge badge_banned"><span class="badge_banned_circle"></span>' . $Translate->get_translate_phrase('_Banned') . '</div>');
						endif; ?>
						<?php if (!empty($Player->Db->db_data['AdminSystem'])):
							$ban_check = $Db->query('AdminSystem', $Db->db_data['AdminSystem'][0]['USER_ID'], $Db->db_data['AdminSystem'][0]['DB_num'], "SELECT `created`, `steamid`, `expires`, `unpunish_admin_id` FROM `as_punishments` WHERE `steamid` = '" . $Player->get_steam_64() . "' AND (server_id = '" . $Player->lws['server_sb_id'] . "' OR server_id = '-1') AND `punish_type` = 0 order by `created` desc limit 1");
							!empty($ban_check) && (empty($ban_check['unpunish_admin_id']) && (($ban_check['expires']) == 0 || $ban_check['expires'] >= time())) && (print '<div class="user_badge badge_banned"><span class="badge_banned_circle"></span>' . $Translate->get_translate_phrase('_Banned') . '</div>');
						endif; ?>
						<?php if (!empty($Player->Db->db_data['SourceBans'])):
							$ban_check = $Db->query('SourceBans', $Db->db_data['SourceBans'][0]['USER_ID'], $Db->db_data['SourceBans'][0]['DB_num'], "SELECT `created`, `authid`, `ends`, `length`, `RemovedBy` FROM `sb_bans` WHERE `authid` LIKE '%" . $Player->get_steam_32_short() . "%' AND (`sid` = '" . $Player->lws['server_sb_id'] . "' OR `sid` = 0) AND `type` = 0 order by `created` desc limit 1");
							!empty($ban_check) && (empty($ban_check['RemovedBy']) && (($ban_check['length']) == 0 || $ban_check['ends'] >= time())) && (print '<div class="user_badge badge_banned"><span class="badge_banned_circle"></span>' . $Translate->get_translate_phrase('_Banned') . '</div>');
						endif; ?>
						<?php if (!empty($Player->Db->db_data['IksAdminNew'])) :
							$ban_check = $Db->query('IksAdminNew', $Db->db_data['IksAdminNew'][0]['USER_ID'], $Db->db_data['IksAdminNew'][0]['DB_num'], "SELECT `created_at`, `steam_id`, `end_at`, `unbanned_by` FROM `iks_bans` WHERE `steam_id` = '" . $Player->get_steam_64() . "' AND (server_id = '" . $Player->lws['server_sb_id'] . "' OR server_id IS NULL) order by `created_at` desc limit 1");
							!empty($ban_check) && (empty($ban_check['unbanned_by']) && (($ban_check['end_at']) == 0 || $ban_check['end_at'] >= time())) && (print '<div class="user_badge badge_banned"><span class="badge_banned_circle"></span>' . $Translate->get_translate_phrase('_Banned') . '</div>');
						endif; ?>
						<?php if (file_exists(SESSIONS . '/blockedusers.json')):
							$site_bans = json_decode(file_get_contents(SESSIONS . '/blockedusers.json'), true) ?>
							<?php if (!empty($site_bans) && in_array($Player->get_steam_64(), array_column($site_bans, 'steam'))): ?>
								<div class="user_badge badge_banned"><span class="badge_banned_circle"></span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_bannedSite') ?>
								</div>
							<?php endif; ?>
						<?php endif; ?>
						<?php if (!empty($roles)): ?>
							<?php foreach ($roles as $role): ?>
								<?php if (in_array($Player->get_steam_64(), $role['users'])): ?>
									<div class="user_badge"
										style="color: <?= $role['color'] ?>; background-color: <?= str_replace(')', ', 10%)', $role['color']) ?>;">
										<span style="background-color: <?= $role['color'] ?>;"></span>
										<?= $role['role'] ?>
									</div>
								<?php endif; ?>
							<?php endforeach; ?>
						<?php endif; ?>
					</div>
					<hr>
					<div class="user_menu_short">
						<a class="<?php if ($page == 'info'): ?>a_active<?php endif; ?>"
							href="<?= $General->arr_general['site'] . 'profiles/' . $profile . '/info/' . $server_page ?>/"
							data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Info'); ?>"
							data-tippy-placement="top">
							<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve" fill-rule="evenodd" class="">
								<g>
									<circle cx="11.5" cy="6.744" r="5.5"></circle>
									<path
										d="M12.925 21.756A6.226 6.226 0 0 1 11.25 17.5c0-1.683.667-3.212 1.751-4.336-.49-.038-.991-.058-1.501-.058-3.322 0-6.263.831-8.089 2.076-1.393.95-2.161 2.157-2.161 3.424v1.45a1.697 1.697 0 0 0 1.7 1.7z">
									</path>
									<path
										d="M17.5 12.25c-2.898 0-5.25 2.352-5.25 5.25s2.352 5.25 5.25 5.25 5.25-2.352 5.25-5.25-2.352-5.25-5.25-5.25zm-.75 5.25V20a.75.75 0 0 0 1.5 0v-2.5a.75.75 0 0 0-1.5 0zm.75-3.25a1 1 0 1 1 0 2 1 1 0 0 1 0-2z">
									</path>
								</g>
							</svg>
							<?php if (!empty($Admins)): ?>
								<a class="<?php if ($page == 'admin'): ?>a_active<?php endif; ?>"
									href="<?= $General->arr_general['site'] . 'profiles/' . $profile . '/admin/' . $server_page ?>/"
									data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Admin'); ?>"
									data-tippy-placement="top">
									<svg x="0" y="0" viewBox="0 0 512 512" xml:space="preserve" class="">
										<g>
											<path
												d="M456.606 79.347 381.653 4.394A15 15 0 0 0 371.047 0H140.953a15 15 0 0 0-10.606 4.394L55.394 79.347A15 15 0 0 0 51 89.953v209.226c0 25.993 6.609 51.777 19.113 74.564 12.504 22.788 30.702 42.212 52.626 56.175l125.203 79.734C250.4 511.217 253.2 512 256 512s5.6-.783 8.058-2.348l125.202-79.734c21.925-13.962 40.123-33.387 52.627-56.175C454.391 350.956 461 325.171 461 299.179V89.953a15 15 0 0 0-4.394-10.606zm-89.569 136.039-41.347 44.926 7.013 60.619a15 15 0 0 1-21.137 15.366L256 310.893l-55.566 25.404a15 15 0 0 1-21.137-15.366l7.013-60.619-41.347-44.926a15 15 0 0 1 8.074-24.862l59.899-12.073 30.001-53.168a15 15 0 0 1 26.128 0l30.001 53.168 59.899 12.073a14.998 14.998 0 0 1 8.072 24.862z">
											</path>
										</g>
									</svg>
								</a>
							<?php endif; ?>
							<a class="<?php if ($page == 'block'): ?>a_active<?php endif; ?>"
								href="<?= $General->arr_general['site'] . 'profiles/' . $profile . '/block/' . $server_page ?>/"
								data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Block'); ?>"
								data-tippy-placement="top">
								<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve" class="">
									<g>
										<path
											d="M19.75 15.426v-.435a2.747 2.747 0 0 0-2.76-2.74 2.743 2.743 0 0 0-2.74 2.74v.435c-.589.282-1 .879-1 1.574v3c0 .965.785 1.75 1.75 1.75h4c.965 0 1.75-.785 1.75-1.75v-3c0-.695-.411-1.292-1-1.574zM17.5 19a.5.5 0 0 1-1 0v-1a.5.5 0 0 1 1 0zm.75-3.75h-2.5v-.26c0-.684.556-1.24 1.26-1.24.684 0 1.24.557 1.24 1.24zm-5.49-.6c.18-2.18 2.01-3.9 4.23-3.9.26 0 .51.02.76.07V5c0-1.52-1.23-2.75-2.75-2.75H6C4.48 2.25 3.25 3.48 3.25 5v13c0 1.52 1.23 2.75 2.75 2.75h5.84c-.06-.24-.09-.49-.09-.75v-3c0-.89.38-1.74 1.01-2.35zM7 5.25h7a.75.75 0 0 1 0 1.5H7a.75.75 0 0 1 0-1.5zm3 7.5H7a.75.75 0 0 1 0-1.5h3a.75.75 0 0 1 0 1.5zm-3-3a.75.75 0 0 1 0-1.5h7a.75.75 0 0 1 0 1.5z">
										</path>
									</g>
								</svg>
							</a>
							<a class="<?php if ($page == 'friends'): ?>a_active<?php endif; ?>"
								href="<?= $General->arr_general['site'] . 'profiles/' . $profile . '/friends/' . $server_page ?>/"
								data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Friends'); ?>"
								data-tippy-placement="top">
								<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve" class="">
									<g>
										<g data-name="Layer 2">
											<circle cx="8" cy="7" r="4.75"></circle>
											<path
												d="M22.75 18A2.748 2.748 0 0 1 20 20.75h-5.46A3.692 3.692 0 0 0 15.75 18a6.668 6.668 0 0 0-1.62-4.37 4.842 4.842 0 0 1 1.87-.38h2A4.754 4.754 0 0 1 22.75 18z">
											</path>
											<path
												d="M9 12.25H7A5.757 5.757 0 0 0 1.25 18 2.752 2.752 0 0 0 4 20.75h8A2.752 2.752 0 0 0 14.75 18 5.757 5.757 0 0 0 9 12.25zM20.75 9a3.746 3.746 0 0 1-7.48.3 5.539 5.539 0 0 0 .47-2.16A3.752 3.752 0 0 1 20.75 9z">
											</path>
										</g>
									</g>
								</svg>
							</a>
							<?php if (isset($_SESSION["steamid"]) && (($Player->get_steam_32() == $_SESSION['steamid32']) || isset($_SESSION['user_admin']))): ?>
								<a class="<?php if ($page == 'transaction'): ?>a_active<?php endif; ?>"
									href="<?= $General->arr_general['site'] . 'profiles/' . $profile . '/transaction/' . $server_page ?>/"
									data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Transaction'); ?>"
									data-tippy-placement="top">
									<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve" class="">
										<g>
											<path
												d="M19.5 3.67c0-.01 0-.02-.02-.03-.22-.28-.51-.43-.85-.43-.53 0-1.17.35-1.86 1.09-.82.88-2.08.81-2.8-.15l-1.01-1.34c-.4-.54-.93-.81-1.46-.81s-1.06.27-1.46.81L9.02 4.16c-.71.95-1.96 1.02-2.78.15l-.01-.01C5.1 3.09 4.09 2.91 3.52 3.64c-.02.01-.02.02-.02.03-.36.77-.5 1.85-.5 3.37v9.92c0 1.52.14 2.6.5 3.37 0 .01.01.03.02.04.58.72 1.58.54 2.71-.67l.01-.01c.82-.87 2.07-.8 2.78.15l1.02 1.35c.4.54.93.81 1.46.81s1.06-.27 1.46-.81l1.01-1.34c.72-.96 1.98-1.03 2.8-.15.69.74 1.33 1.09 1.86 1.09.34 0 .63-.14.85-.42.01-.01.02-.03.02-.04.36-.77.5-1.85.5-3.37V7.04c0-1.52-.14-2.6-.5-3.37zM14 14.5H8c-.41 0-.75-.34-.75-.75S7.59 13 8 13h6c.41 0 .75.34.75.75s-.34.75-.75.75zm2-3.5H8c-.41 0-.75-.34-.75-.75s.34-.75.75-.75h8c.41 0 .75.34.75.75s-.34.75-.75.75z">
											</path>
										</g>
									</svg>
								</a>
							<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<?php require_once MODULES . 'module_page_profiles/includes/' . $page . '.php'; ?>
</div>
<?php if (isset($_SESSION["steamid64"]) && ($Player->get_steam_64() == $_SESSION['steamid64'])) : ?>
	<div class="popup_modal" id="profileSettings">
		<div class="popup_modal_content no-close no-scrollbar">
			<div class="popup_modal_head">
				<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Settings_profile'); ?>
			</div>
			<div class="contact_body">
				<form id="options_one" enctype="multipart/form-data" method="post" class="settings_container">
					<div class="settings_inp_inf">
						<input type="hidden" name="edit_info">
						<div class="flex-inline">
							<div class="inputs-inline">
								<label for="vk"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Vkontakte'); ?></label>
								<input id="vk" placeholder="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Vk_id'); ?>" name="vk" value="<?= action_text_clear($Info['vk']) ?? '' ?>">
							</div>
							<div class="inputs-inline">
								<label for="discord">Discord</label>
								<input id="discord" placeholder="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_DS_nickname'); ?>" name="discord" value="<?= action_text_clear($Info['discord']) ?? '' ?>">
							</div>
						</div>
						<div class="flex-inline">
							<div class="inputs-inline">
								<label for="telegram">Telegram</label>
								<input id="telegram" placeholder="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_TG_nickname'); ?>" name="telegram" value="<?= action_text_clear($Info['tg']) ?? '' ?>">
							</div>
							<div class="inputs-inline">
								<label for="twitch">Twitch</label>
								<input id="twitch" placeholder="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_twitch_nickname'); ?>" name="twitch" value="<?= action_text_clear($Info['twitch']) ?? '' ?>">
							</div>
						</div>
						<div class="inputs-inline">
							<label for="aboutUs"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_User_status') ?></label>
							<textarea name="status" id="aboutUs" placeholder="<?= $Translate->get_translate_module_phrase('module_page_profiles', '_AboutMe') ?>"><?= action_text_clear($Info['status']) ?? '' ?></textarea>
						</div>
						<button class='width-100' type="submit" form="options_one"><?php echo $Translate->get_translate_module_phrase('module_page_adminpanel', '_Save') ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
<?php endif; ?>