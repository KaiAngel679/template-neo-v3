<div class="col-md-9">
	<div class="card">
		<div class="card-header">
			<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Friends'); ?></div>
		</div>
		<div class="card-container">
			<?php if (!empty($friends)) : ?>
				<div class="friends_list no-scrollbar">
					<?php foreach ($friends['friendslist']['friends'] as $friend) : ?>
						<?php
						switch ($Player->found[$Player->server_group]['DB_mod']) {
							case 'LevelsRanks':
								$checkDB = $Db->query('LevelsRanks', $Player->found[$Player->server_group]['USER_ID'], $Player->found[$Player->server_group]['DB'], "SELECT `name`, `lastconnect` FROM `" . $Player->found[$Player->server_group]['Table'] . "` WHERE `steam` = '" . con_steam64to32($friend['steamid']) . "' AND  `lastconnect` > 0");
							break;
						} ?>
						<?php if (!empty($checkDB)) : ?>
							<div class="friend_card" onclick="window.open('<?= $General->arr_general['site'] ?>profiles/<?= $friend['steamid']; ?>/?search=1');">
								<div>
									<?php $General->get_js_relevance_avatar($friend['steamid']) ?>
									<img src="<?= $General->getAvatar($friend['steamid'], 3) ?>" id="avatar" avatarid="<?= $friend['steamid']; ?>">
									<div class="user_online_status" style="<?php if ($General->checkOnline($friend['steamid']) == 1) { echo 'display: block;'; } ?>"></div>
								</div>
								<div class="friend_info">
									<span class="friend_nick"><?= action_text_clear($checkDB['name']) ?></span>
									<span class="friend_lc"><?= $Translate->get_translate_module_phrase('module_page_profiles', '_Friends_played'); ?> <?= date("d.m.Y, H:i", $checkDB['lastconnect']) ?></span>
								</div>
							</div>
						<?php endif; ?>
					<?php endforeach ?>
				</div>
			<?php else : ?>
				<div class="empty_friends">
					<?= $Translate->get_translate_module_phrase('module_page_profiles', '_Steam_friends_list_is_empty_or_hidden'); ?>
				<?php endif; ?>
				</div>
		</div>
	</div>
</div>