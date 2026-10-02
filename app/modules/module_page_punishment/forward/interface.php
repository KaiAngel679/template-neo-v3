
<div class="row">
	<div class="col-md-3 fix-width-tablet">
		<div class="card sticky-filter">
			<div class="card-header">
				<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Info'); ?></div>
			</div>
			<div class="card-container">
				<div class="punishment-filters">
					<span class="punish_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_FilterPunishes'); ?></span>
					<div class="segmented-control">
						<span class="selection"></span>
						<div class="option">
							<input type="radio" id="bans" name="sample" value="bans" checked>
							<label for="bans"><span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Bans'); ?></span></label>
						</div>
						<div class="option">
							<input type="radio" id="comms" name="sample" value="comms">
							<label for="comms"><span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Comms'); ?></span></label>
						</div>
						<div class="option">
							<input type="radio" id="admins" name="sample" value="admins">
							<label for="admins"><span><?= $Translate->get_translate_phrase('_Admins_sb'); ?></span></label>
						</div>
					</div>
					<?php if (!empty($Db->db_data['SourceBans']) && (!empty($Db->db_data['IksAdmin']) || !empty($Db->db_data['AdminSystem']) || !empty($Db->db_data['IksAdminNew']))): ?>
						<div class="toggle__game-wrapper">
							<label class="toggle__game-custom-radio">
								<input type="radio" onclick="window.location.href=this.value" type="radio" value="<?= $General->arr_general['site'] ?>punishment/cs2/" <?= ($game == 'cs2') ? 'checked' : '' ?>>
								<span>	
									<svg><use href="/resources/img/sprite.svg#cs2"></use></svg> CS2
								</span>
							</label>
							<label class="toggle__game-custom-radio">
								<input type="radio" onclick="window.location.href=this.value" type="radio" value="<?= $General->arr_general['site'] ?>punishment/csgo/" <?= ($game == 'csgo') ? 'checked' : '' ?>>
								<span>
									<svg><use href="/resources/img/sprite.svg#csgo"></use></svg> CS:GO
								</span>
							</label>
						</div>
					<?php endif; ?>
					<div id="search">
						<input type="text" placeholder="" readionly>
					</div>
					<div class="adaptive-select-wrapper">
						<ul class="adaptive-select__dropdown-list" id="option-server-select">
							<li>
								<label class="adaptive-select__label" for="for_server_all">
									<div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_AllServers'); ?></div>
									<input class="hide-input" id="for_server_all" type="radio" name="server" onclick="window.location.href=this.value" value="<?= $General->arr_general['site'] ?>punishment/<?= $game ?>/all/" <?= ($server_id === 'all') ? 'checked' : '' ?>>
								</label>
							</li>
							<?php if ($Punishment->GetSettings()['punishment_all_servers'] == 0):
								for ($b = 0, $_c = sizeof($Punishment->GetServerLR()); $b < $_c; $b++): ?>
									<li>
										<label class="adaptive-select__label" for="for_server_<?= $b ?>">
											<div class="adaptive-select__label-text"><?= $Punishment->GetServerLR()[$b]['name'] ?></div>
											<input class="hide-input" id="for_server_<?= $b ?>" name="server" onclick="window.location.href=this.value" type="radio" value="<?= $General->arr_general['site'] ?>punishment/<?= $game ?>/<?= $b ?>" <?= ($server_id === (string)$b) ? 'checked' : '' ?>>
										</label>
									</li>
							<?php endfor;
							endif; ?>
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
				</div>
				<hr>
				<div class="punish_state">
					<span
						class="punish_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_StatusAcc'); ?></span>
					<?php if (empty($_SESSION['steamid64'])): ?>
						<div class="not_auth_user">
							<?= $Translate->get_translate_module_phrase('module_page_punishment', '_DataNotAuth'); ?>
						</div>
					<?php else: ?>
						<?php if (empty($Punishment->GetInfoCount()['my_count_bans']) && empty($Punishment->GetInfoCount()['my_count_mutes'])): ?>
							<div class="no_user_punish">
								<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_NoGameBlocks'); ?></span>
								<svg>
									<use href="/resources/img/sprite.svg#shield-check"></use>
								</svg>
							</div>
						<?php endif; ?>
						<?php if (!empty($Punishment->GetInfoCount()['my_count_bans'])): ?>
							<div class="have_ban_user_punish">
								<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_GameBlocks'); ?>
									<?= $Punishment->GetInfoCount()['my_count_bans'] ?></span>
								<svg>
									<use href="/resources/img/sprite.svg#user-block"></use>
								</svg>
							</div>
						<?php endif;
						if (!empty($Punishment->GetInfoCount()['my_count_mutes'])): ?>
							<div class="have_comm_user_punish">
								<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_CommunicateBlocks'); ?>
									<?= $Punishment->GetInfoCount()['my_count_mutes'] ?></span>
								<svg>
									<use href="/resources/img/sprite.svg#micro-slash"></use>
								</svg>
							</div>
						<?php endif; ?>
					<?php endif;
					if (empty($_SESSION['steamid64'])): ?>
						<button class="width-100"
							onclick="location.href='?auth=login'"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Authorize'); ?></button>
					<?php endif; ?>
					<hr>
					<div class="punish_stats_head">
						<span
							class="punish_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_PunishStats'); ?></span>
						<span
							class="hide_stats"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Hide'); ?></span>
						<span
							class="show_stats"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Show'); ?></span>
					</div>
					<div class="punish_stats_list">
						<div class="punish_string"><span
								class="stats_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_TotalBans'); ?></span><span
								class="stats_count"><?= $Punishment->GetInfoCount()['count_bans'] ?></span></div>
						<div class="punish_string"><span
								class="stats_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_ActiveBans'); ?></span><span
								class="stats_count"><?= $Punishment->GetInfoCount()['count_bans_activ'] ?></span></div>
						<div class="punish_string"><span
								class="stats_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_PermanentBans'); ?></span><span
								class="stats_count"><?= $Punishment->GetInfoCount()['count_bans_perm'] ?></span></div>
						<div class="punish_string"><span
								class="stats_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_TotalComms'); ?></span><span
								class="stats_count"><?= $Punishment->GetInfoCount()['count_mutes'] ?></span></div>
						<div class="punish_string"><span
								class="stats_title"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_TotalGags'); ?></span><span
								class="stats_count"><?= $Punishment->GetInfoCount()['count_gags'] ?></span></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-9 fix-width-tablet">
		<div class="card">
			<div class="card-header" id="punishments_title">
				<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_PunishmentList'); ?>
				</div>
			</div>
			<div class="card-header" style="display: none;" id="admins_title">
				<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_AdminsList'); ?>
				</div>
			</div>
			<div class="card-container">
				<div class="punishments-admins" id="admins_list">
					<?php if (isset($_SESSION['user_admin']) && !$Db->mysql_table_search('Core', 0, 0, 'lvl_web_admins_rating')  && !$Db->mysql_table_search('Core', 0, 0, 'lvl_web_admins_ratingvotes')): ?>
						<button class="width-100" id="punishmentInstallTable"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_setupTables'); ?></button>
						<hr>
					<?php endif; ?>
					<div class="punishments-admins punishmen-admins__wrapper" id="admins_list_search"></div>
					<div class="punishments-admins punishmen-admins__wrapper" id="admins_content"></div>
					<div id="admins_pagination"></div>
					<div class="popup_modal" id="adminModal">
						<div class="popup_modal_content no-close no-scrollbar">
							<div class="popup_modal_head">
								<?= $Translate->get_translate_module_phrase('module_page_punishment', '_adminInfo'); ?>
								<span class="popup_modal_close">
									<svg>
										<use href="/resources/img/sprite.svg#x"></use>
									</svg>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="modern_table" id="punishments_list">
					<div class="punish_header">
						<li>
							<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_TypePunish'); ?></span>
							<span class="none_span"><svg>
									<use href="/resources/img/sprite.svg#hexagom-image"></use>
								</svg></span>
							<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Player'); ?></span>
							<span><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Reason'); ?></span>
							<span
								class="none_span"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Term'); ?></span>
							<span
								class="none_span"><?= $Translate->get_translate_module_phrase('module_page_punishment', '_Admin'); ?></span>
						</li>
					</div>
					<div class="popup_modal" id="punishModal">
						<div class="popup_modal_content no-close no-scrollbar">
							<div class="popup_modal_head">
								<?= $Translate->get_translate_module_phrase('module_page_punishment', '_DetailsPunishment'); ?>
								<span class="popup_modal_close">
									<svg>
										<use href="/resources/img/sprite.svg#x"></use>
									</svg>
								</span>
							</div>
						</div>
					</div>
				</div>
				<div class="punish_content" id="punishment_list_search"></div>
				<div class="punish_content" id="punishment_content"></div>
				<div id="punishment_pagination"></div>
			</div>
		</div>
	</div>
</div>