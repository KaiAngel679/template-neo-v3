
<div class="col-md-9">
	<div class="card">
		<div class="card-header">
			<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Block'); ?></div>
		</div>
		<div class="card-container">
			<div class="tables_info">
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 24 24" xml:space="preserve">
							<g>
								<path
									d="M6.009 6.5a4 4 0 1 1 4 4 4 4 0 0 1-4-4zM11.75 18a6.213 6.213 0 0 1 2.22-4.77.312.312 0 0 0 .09-.21c0-.2-.252-.287-.3-.3A6.778 6.778 0 0 0 12 12.5H8c-4.06 0-5.5 2.97-5.5 5.52 0 2.28 1.21 3.48 3.5 3.48h6.29a.305.305 0 0 0 .309-.3.248.248 0 0 0-.029-.132A6.178 6.178 0 0 1 11.75 18zm11 0A4.75 4.75 0 1 1 18 13.25 4.756 4.756 0 0 1 22.75 18zm-7.508 1.7 4.458-4.458a3.233 3.233 0 0 0-4.458 4.458zM21.25 18a3.213 3.213 0 0 0-.492-1.7L16.3 20.758A3.233 3.233 0 0 0 21.25 18z"
									fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans'); ?>
						(<?= count($Bans) ?>)
					</div>
					<div class="bans_comms_container">
						<?php if ($Bans != true): ?>
							<div class="empty_blocks">
								<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_No_Bans'); ?>
							</div>
						<?php else: ?>
							<div class="bans_comms_header">
								<ul>
									<li>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Data'); ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Reason'); ?></span>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Admin'); ?></span>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Length'); ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Expiration'); ?></span>
									</li>
								</ul>
							</div>
							<div class="bans_comms_content">
								<ul class="no-scrollbar">
									<?php if (!empty($Db->db_data['IksAdmin']) && $Player->lws['server_sb'][0] == 'IksAdmin'):
										foreach ($Bans as $key): ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this"><a
														href="<?= $General->arr_general['site'] ?>profiles/<?= $key['adminsid'] ?>/?search=1"><?= empty($General->checkName($key['adminsid'])) ? $Translate->get_translate_module_phrase('module_page_profiles', '_Unknown') : action_text_clear($General->checkName($key['adminsid'])) ?></a></span>
												<span class="hide_this">
													<?php
													if ($key['time'] == '0' && $key['Unbanned'] != '1') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['Unbanned'] == '1') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end'] && $key['time'] != '0') {
														echo $Modules->action_time_exchange($key['time']);
													} else {
														echo $Modules->action_time_exchange($key['time']);
													}
													?>
												</span>
												<span class="exp_badge <?php if ($key['time'] == '0' && $key['Unbanned'] != '1') {
																									echo 'perm';
																								} elseif ($key['Unbanned'] == '1') {
																									echo 'unbanned';
																								} elseif (time() >= $key['end'] && $key['time'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['time'] == '0' && $key['Unbanned'] != '1') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['Unbanned'] == '1') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end'] && $key['time'] != '0') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['created']) + ($key['time']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['IksAdminNew']) && $Player->lws['server_sb'][0] == 'IksAdminNew'):
										foreach ($Bans as $key): ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created_at']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid']) && $key['admin_steamid'] !== "CONSOLE"): ?>
														<a
															href="<?= $General->arr_general['site'] ?>profiles/<?= $key['admin_steamid'] ?>/?search=1">
															<?= empty($General->checkName($key['admin_steamid'])) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName($key['admin_steamid'])) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="hide_this">
													<?php
													if ($key['end_at'] == '0' && empty($key['unbanned_by'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unbanned_by'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end_at']) {
														echo $Modules->action_time_exchange($key['end_at'] - $key['created_at']);
													} else {
														echo $Modules->action_time_exchange($key['end_at'] - time());
													}
													?>
												</span>
												<span class="exp_badge <?php if ($key['end_at'] == '0' && empty($key['unbanned_by'])) {
																									echo 'perm';
																								} elseif (!empty($key['unbanned_by'])) {
																									echo 'unbanned';
																								} elseif (time() >= $key['end_at'] && $key['end_at'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['end_at'] == '0' && empty($key['unbanned_by'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unbanned_by'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end_at']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['end_at']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['AdminSystem']) && $Player->lws['server_sb'][0] == 'AdminSystem'):
										foreach ($Bans as $key): ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid'])): ?>
														<a
															href="<?= $General->arr_general['site'] ?>profiles/<?= $key['admin_steamid'] ?>/?search=1">
															<?= empty($General->checkName($key['admin_steamid'])) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName($key['admin_steamid'])) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="hide_this">
													<?php
													if ($key['expires'] == '0' && empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['expires']) {
														echo $Modules->action_time_exchange($key['expires'] - $key['created']);
													} else {
														echo $Modules->action_time_exchange($key['expires'] - time());
													}
													?>
												</span>
												<span class="exp_badge <?php if ($key['expires'] == '0' && empty($key['unpunish_admin_id'])) {
																									echo 'perm';
																								} elseif (!empty($key['unpunish_admin_id'])) {
																									echo 'unbanned';
																								} elseif (time() >= $key['expires'] && $key['expires'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['expires'] == '0' && empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['expires']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['expires']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['SourceBans']) && $Player->lws['server_sb'][0] == 'SourceBans'):
										foreach ($Bans as $key): ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid']) && $key['admin_steamid'] != "STEAM_ID_SERVER"): ?>
														<a
															href="<?= $General->arr_general['site'] ?>profiles/<?= $key['admin_steamid'] ?>/?search=1">
															<?= empty($General->checkName(con_steam64($key['admin_steamid']))) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName(con_steam64($key['admin_steamid']))) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="hide_this">
													<?php
													if ($key['length'] == '0' && $key['RemoveType'] != 'U') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['RemoveType'] == 'U') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['ends'] && $key['length'] != '0') {
														echo $Modules->action_time_exchange($key['ends'] - $key['created']);
													} else {
														echo $Modules->action_time_exchange($key['ends'] - time());
													}
													?>
												</span>
												<span class="exp_badge <?php if ($key['length'] == '0' && $key['RemoveType'] != 'U') {
																									echo 'perm';
																								} elseif ($key['RemoveType'] == 'U') {
																									echo 'unbanned';
																								} elseif (time() >= $key['ends'] && $key['length'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['length'] == '0' && $key['RemoveType'] != 'U') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['RemoveType'] == 'U') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['ends']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['ends']) - time());
													}
													?>
												</span>
											</li>
									<?php endforeach;
									endif; ?>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<div class="fill_blocks">
					<div class="title_head">
						<svg x="0" y="0" viewBox="0 0 475.092 475.092" xml:space="preserve">
							<g>
								<path
									d="M113.922 269.803c-2.856-11.419-4.283-22.172-4.283-32.26v-36.55c0-4.947-1.809-9.229-5.424-12.847-3.617-3.616-7.898-5.424-12.847-5.424-4.952 0-9.235 1.809-12.851 5.424-3.617 3.617-5.426 7.9-5.426 12.847v36.547c0 21.129 3.999 41.494 11.993 61.106l28.838-28.843zM237.545 328.897c25.126 0 46.638-8.946 64.521-26.83 17.891-17.884 26.837-39.399 26.837-64.525v-36.547L431.972 97.929c1.902-1.903 2.854-4.093 2.854-6.567s-.952-4.664-2.854-6.567l-23.407-23.413c-1.91-1.906-4.097-2.856-6.57-2.856-2.472 0-4.661.95-6.564 2.856L43.117 413.698c-1.903 1.902-2.852 4.093-2.852 6.563 0 2.478.949 4.668 2.852 6.57l23.411 23.411c1.904 1.903 4.095 2.851 6.567 2.851 2.475 0 4.665-.947 6.567-2.851l72.519-72.519c20.933 12.949 43.299 20.656 67.093 23.127v37.691h-73.089c-4.949 0-9.235 1.811-12.847 5.428-3.618 3.613-5.43 7.898-5.43 12.847 0 4.941 1.812 9.233 5.43 12.847 3.612 3.614 7.898 5.428 12.847 5.428h182.718c4.948 0 9.232-1.813 12.847-5.428 3.62-3.613 5.428-7.905 5.428-12.847 0-4.948-1.808-9.233-5.428-12.847-3.614-3.617-7.898-5.428-12.847-5.428h-73.087V400.85c41.302-4.565 75.988-22.408 104.067-53.526 28.072-31.117 42.11-67.711 42.11-109.776v-36.554c0-4.947-1.808-9.229-5.421-12.845-3.621-3.617-7.902-5.426-12.851-5.426-4.945 0-9.229 1.809-12.847 5.426-3.617 3.616-5.424 7.898-5.424 12.845v36.547c0 35.214-12.519 65.333-37.545 90.359s-55.151 37.544-90.362 37.544c-20.557 0-40.065-4.849-58.529-14.561l27.408-27.401c10.285 3.615 20.657 5.415 31.123 5.415zM290.223 16.849C274.518 5.618 256.959 0 237.545 0c-25.125 0-46.635 8.951-64.524 26.84-17.89 17.89-26.835 39.399-26.835 64.525v146.177L323.483 60.244c-6.475-17.701-17.556-32.167-33.26-43.395z"
									fill="var(--text-default)"></path>
							</g>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Comms'); ?>
						(<?= count($Comms) ?>)
					</div>
					<div class="bans_comms_container">
						<?php if ($Comms != true): ?>
							<div class="empty_blocks">
								<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Comms_No_Mute'); ?>
							</div>
						<?php else: ?>
							<div class="bans_comms_header no-scrollbar">
								<ul>
									<li>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Data'); ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Reason'); ?></span>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Bans_Admin'); ?></span>
										<span
											class="hide_this"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Comms_Type'); ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Expiration'); ?></span>
									</li>
								</ul>
							</div>
							<div class="bans_comms_content">
								<ul class="no-scrollbar">
									<?php if (!empty($Db->db_data['IksAdmin']) && $Player->lws['server_sb'][0] == 'IksAdmin'):
										foreach ($Comms as $key): ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this"><a
														href="<?= $General->arr_general['site'] ?>profiles/<?= $key['adminsid'] ?>/?search=1"><?= empty($General->checkName($key['adminsid'])) ? $Translate->get_translate_module_phrase('module_page_profiles', '_Unknown') : action_text_clear($General->checkName($key['adminsid'])) ?></a></span>
												<?php $MuteType = $Db->queryAll('IksAdmin', $Player->lws['server_sb'][1], $Player->lws['server_sb'][2], "SELECT COUNT(*) as count FROM `iks_mutes` WHERE `sid` = " . $key['sid'] . " AND `created` = " . $key['created'] . ";");
												$ChatType = $Db->queryAll('IksAdmin', $Player->lws['server_sb'][1], $Player->lws['server_sb'][2], "SELECT COUNT(*) as count FROM `iks_gags` WHERE `sid` = " . $key['sid'] . " AND `created` = " . $key['created'] . ";");
												$MuteCount = $MuteType[0]['count'];
												$ChatCount = $ChatType[0]['count'];
												if ($MuteCount > 0 && $ChatCount > 0) {
													$punishmentType = '<svg viewBox="0 0 512 512"><path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM512 256c0 141.4-114.6 256-256 256S0 397.4 0 256S114.6 0 256 0S512 114.6 512 256z"></path></svg>';
												} elseif ($MuteCount > 0) {
													$punishmentType = '<svg viewBox="0 0 640 512"><path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 21.2-5.1 41.1-14.2 58.7L416 300.8V96c0-53-43-96-96-96s-96 43-96 96v54.3L38.8 5.1zM344 430.4c20.4-2.8 39.7-9.1 57.3-18.2l-43.1-33.9C346.1 382 333.3 384 320 384c-70.7 0-128-57.3-128-128v-8.7L144.7 210c-.5 1.9-.7 3.9-.7 6v40c0 89.1 66.2 162.7 152 174.4V464H248c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H344V430.4z"></path></svg>';
												} elseif ($ChatCount > 0) {
													$punishmentType = '<svg viewBox="0 0 640 512"><path d="M64.03 239.1c0 49.59 21.38 94.1 56.97 130.7c-12.5 50.39-54.31 95.3-54.81 95.8c-2.187 2.297-2.781 5.703-1.5 8.703c1.312 3 4.125 4.797 7.312 4.797c66.31 0 116-31.8 140.6-51.41c32.72 12.31 69.02 19.41 107.4 19.41c37.39 0 72.78-6.663 104.8-18.36L82.93 161.7C70.81 185.9 64.03 212.3 64.03 239.1zM630.8 469.1l-118.1-92.59C551.1 340 576 292.4 576 240c0-114.9-114.6-207.1-255.1-207.1c-67.74 0-129.1 21.55-174.9 56.47L38.81 5.117C28.21-3.154 13.16-1.096 5.115 9.19C-3.072 19.63-1.249 34.72 9.188 42.89l591.1 463.1c10.5 8.203 25.57 6.333 33.7-4.073C643.1 492.4 641.2 477.3 630.8 469.1z"></path></svg>';
												} ?>
												<span class="comms_svg hide_this">
													<?= $punishmentType ?>
												</span>
												<span class="exp_badge <?php if ($key['time'] == '0' && $key['Unbanned'] != '1') {
																									echo 'perm';
																								} elseif ($key['Unbanned'] == '1') {
																									echo 'unbanned';
																								} elseif (time() >= $key['end'] && $key['time'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['time'] == '0' && $key['Unbanned'] != '1') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['Unbanned'] == '1') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end'] && $key['time'] != '0') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['created']) + ($key['time']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['IksAdminNew']) && $Player->lws['server_sb'][0] == 'IksAdminNew'):
										foreach ($Comms as $key) : ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created_at']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid']) && $key['admin_steamid'] !== "CONSOLE"): ?>
														<a href="<?= $General->arr_general['site'] ?>profiles/<?= $key['admin_steamid'] ?>/?search=1">
															<?= empty($General->checkName($key['admin_steamid'])) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName($key['admin_steamid'])) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="comms_svg hide_this">
													<?php if ($key['mute_type'] == 2): ?>
														<svg viewBox="0 0 512 512">
															<path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM512 256c0 141.4-114.6 256-256 256S0 397.4 0 256S114.6 0 256 0S512 114.6 512 256z"></path>
														</svg>
													<?php elseif ($key['mute_type'] == 1): ?>
														<svg viewBox="0 0 640 512">
															<path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 21.2-5.1 41.1-14.2 58.7L416 300.8V96c0-53-43-96-96-96s-96 43-96 96v54.3L38.8 5.1zM344 430.4c20.4-2.8 39.7-9.1 57.3-18.2l-43.1-33.9C346.1 382 333.3 384 320 384c-70.7 0-128-57.3-128-128v-8.7L144.7 210c-.5 1.9-.7 3.9-.7 6v40c0 89.1 66.2 162.7 152 174.4V464H248c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H344V430.4z"></path>
														</svg>
													<?php elseif ($key['mute_type'] == 0): ?>
														<svg viewBox="0 0 640 512">
															<path d="M64.03 239.1c0 49.59 21.38 94.1 56.97 130.7c-12.5 50.39-54.31 95.3-54.81 95.8c-2.187 2.297-2.781 5.703-1.5 8.703c1.312 3 4.125 4.797 7.312 4.797c66.31 0 116-31.8 140.6-51.41c32.72 12.31 69.02 19.41 107.4 19.41c37.39 0 72.78-6.663 104.8-18.36L82.93 161.7C70.81 185.9 64.03 212.3 64.03 239.1zM630.8 469.1l-118.1-92.59C551.1 340 576 292.4 576 240c0-114.9-114.6-207.1-255.1-207.1c-67.74 0-129.1 21.55-174.9 56.47L38.81 5.117C28.21-3.154 13.16-1.096 5.115 9.19C-3.072 19.63-1.249 34.72 9.188 42.89l591.1 463.1c10.5 8.203 25.57 6.333 33.7-4.073C643.1 492.4 641.2 477.3 630.8 469.1z"></path>
														</svg>
													<?php endif; ?>
												</span>
												<span class="exp_badge <?php if ($key['end_at'] == '0' && empty($key['unbanned_by'])) {
																									echo 'perm';
																								} elseif (!empty($key['unbanned_by'])) {
																									echo 'unbanned';
																								} elseif (time() >= $key['end_at'] && $key['end_at'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['end_at'] == '0' && empty($key['unbanned_by'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unbanned_by'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['end_at']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['end_at']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['AdminSystem']) && $Player->lws['server_sb'][0] == 'AdminSystem') :
										foreach ($Comms as $key) : ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid'])): ?>
														<a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam64($key['admin_steamid']) ?>/?search=1">
															<?= empty($General->checkName(con_steam64($key['admin_steamid']))) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName(con_steam64($key['admin_steamid']))) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="comms_svg hide_this">
													<?php if ($key['punish_type'] == 3): ?>
														<svg viewBox="0 0 512 512">
															<path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM512 256c0 141.4-114.6 256-256 256S0 397.4 0 256S114.6 0 256 0S512 114.6 512 256z"></path>
														</svg>
													<?php elseif ($key['punish_type'] == 2): ?>
														<svg viewBox="0 0 640 512">
															<path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 21.2-5.1 41.1-14.2 58.7L416 300.8V96c0-53-43-96-96-96s-96 43-96 96v54.3L38.8 5.1zM344 430.4c20.4-2.8 39.7-9.1 57.3-18.2l-43.1-33.9C346.1 382 333.3 384 320 384c-70.7 0-128-57.3-128-128v-8.7L144.7 210c-.5 1.9-.7 3.9-.7 6v40c0 89.1 66.2 162.7 152 174.4V464H248c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H344V430.4z"></path>
														</svg>
													<?php elseif ($key['punish_type'] == 1): ?>
														<svg viewBox="0 0 640 512">
															<path d="M64.03 239.1c0 49.59 21.38 94.1 56.97 130.7c-12.5 50.39-54.31 95.3-54.81 95.8c-2.187 2.297-2.781 5.703-1.5 8.703c1.312 3 4.125 4.797 7.312 4.797c66.31 0 116-31.8 140.6-51.41c32.72 12.31 69.02 19.41 107.4 19.41c37.39 0 72.78-6.663 104.8-18.36L82.93 161.7C70.81 185.9 64.03 212.3 64.03 239.1zM630.8 469.1l-118.1-92.59C551.1 340 576 292.4 576 240c0-114.9-114.6-207.1-255.1-207.1c-67.74 0-129.1 21.55-174.9 56.47L38.81 5.117C28.21-3.154 13.16-1.096 5.115 9.19C-3.072 19.63-1.249 34.72 9.188 42.89l591.1 463.1c10.5 8.203 25.57 6.333 33.7-4.073C643.1 492.4 641.2 477.3 630.8 469.1z"></path>
														</svg>
													<?php endif; ?>
												</span>
												<span class="exp_badge <?php if ($key['expires'] == '0' && empty($key['unpunish_admin_id'])) {
																									echo 'perm';
																								} elseif (!empty($key['unpunish_admin_id'])) {
																									echo 'unbanned';
																								} elseif (time() >= $key['expires'] && $key['expires'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['expires'] == '0' && empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif (!empty($key['unpunish_admin_id'])) {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['expires']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['expires']) - time());
													}
													?>
												</span>
											</li>
										<?php endforeach;
									elseif (!empty($Db->db_data['SourceBans']) && $Player->lws['server_sb'][0] == 'SourceBans') : 
									foreach ($Comms as $key) : ?>
											<li>
												<span class="hide_this"><?= date("d.m.Y", $key['created']) ?></span>
												<span><?= $key['reason'] ?></span>
												<span class="hide_this">
													<?php if (!empty($key['admin_steamid']) && $key['admin_steamid'] != "STEAM_ID_SERVER"): ?>
														<a href="<?= $General->arr_general['site'] ?>profiles/<?= con_steam64($key['admin_steamid']) ?>/?search=1">
															<?= empty($General->checkName(con_steam64($key['admin_steamid']))) ? action_text_clear($key['admin_name']) : action_text_clear($General->checkName(con_steam64($key['admin_steamid']))) ?>
														</a>
													<?php else: ?>
														CONSOLE
													<?php endif; ?>
												</span>
												<span class="comms_svg hide_this">
													<?php if ($key['type'] == 3): ?>
														<svg viewBox="0 0 512 512">
															<path d="M367.2 412.5L99.5 144.8C77.1 176.1 64 214.5 64 256c0 106 86 192 192 192c41.5 0 79.9-13.1 111.2-35.5zm45.3-45.3C434.9 335.9 448 297.5 448 256c0-106-86-192-192-192c-41.5 0-79.9 13.1-111.2 35.5L412.5 367.2zM512 256c0 141.4-114.6 256-256 256S0 397.4 0 256S114.6 0 256 0S512 114.6 512 256z"></path>
														</svg>
													<?php elseif ($key['type'] == 2): ?>
														<svg viewBox="0 0 640 512">
															<path d="M38.8 5.1C28.4-3.1 13.3-1.2 5.1 9.2S-1.2 34.7 9.2 42.9l592 464c10.4 8.2 25.5 6.3 33.7-4.1s6.3-25.5-4.1-33.7L472.1 344.7c15.2-26 23.9-56.3 23.9-88.7V216c0-13.3-10.7-24-24-24s-24 10.7-24 24v40c0 21.2-5.1 41.1-14.2 58.7L416 300.8V96c0-53-43-96-96-96s-96 43-96 96v54.3L38.8 5.1zM344 430.4c20.4-2.8 39.7-9.1 57.3-18.2l-43.1-33.9C346.1 382 333.3 384 320 384c-70.7 0-128-57.3-128-128v-8.7L144.7 210c-.5 1.9-.7 3.9-.7 6v40c0 89.1 66.2 162.7 152 174.4V464H248c-13.3 0-24 10.7-24 24s10.7 24 24 24h72 72c13.3 0 24-10.7 24-24s-10.7-24-24-24H344V430.4z"></path>
														</svg>
													<?php elseif ($key['type'] == 1): ?>
														<svg viewBox="0 0 640 512">
															<path d="M64.03 239.1c0 49.59 21.38 94.1 56.97 130.7c-12.5 50.39-54.31 95.3-54.81 95.8c-2.187 2.297-2.781 5.703-1.5 8.703c1.312 3 4.125 4.797 7.312 4.797c66.31 0 116-31.8 140.6-51.41c32.72 12.31 69.02 19.41 107.4 19.41c37.39 0 72.78-6.663 104.8-18.36L82.93 161.7C70.81 185.9 64.03 212.3 64.03 239.1zM630.8 469.1l-118.1-92.59C551.1 340 576 292.4 576 240c0-114.9-114.6-207.1-255.1-207.1c-67.74 0-129.1 21.55-174.9 56.47L38.81 5.117C28.21-3.154 13.16-1.096 5.115 9.19C-3.072 19.63-1.249 34.72 9.188 42.89l591.1 463.1c10.5 8.203 25.57 6.333 33.7-4.073C643.1 492.4 641.2 477.3 630.8 469.1z"></path>
														</svg>
													<?php endif; ?>
												</span>
												<span class="exp_badge <?php if ($key['length'] == '0' && $key['RemoveType'] != 'U') {
																									echo 'perm';
																								} elseif ($key['RemoveType'] == 'U') {
																									echo 'unbanned';
																								} elseif (time() >= $key['ends'] && $key['ends'] != '0') {
																									echo 'expired';
																								} else {
																									echo '';
																								} ?>">
													<?php
													if ($key['length'] == '0' && $key['RemoveType'] != 'U') {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Permanent');
													} elseif ($key['RemoveType'] == 'U') {
														echo $Translate->get_translate_phrase('_Unban');
													} elseif (time() >= $key['ends']) {
														echo $Translate->get_translate_module_phrase('module_page_profiles', '_Expired');
													} else {
														echo $Modules->action_time_exchange_exact(($key['ends']) - time());
													}
													?>
												</span>
											</li>
									<?php endforeach;
									endif; ?>
								</ul>
								</ul>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>