<div class="row">
	<div class="col-md-6">
		<div class="card height-100">
			<div class="card-container">
				<div class="accesses">
					<form class="online_settings" id="addServer" method="POST">
						<div class="flex-inline">
							<div class="inputs-inline">
								<label for="id_server_name"><?= $Translate->get_translate_phrase('_Server') ?></label>
								<input id="id_server_name" type="text" name="server_name" placeholder="#1 -> MIRAGE">
							</div>
							<div class="inputs-inline">
								<label for="idsrv"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_iksServerID') ?></label>
								<input id="idsrv" type="number" name="server_id" placeholder="1" value="1">
							</div>
						</div>
						<button class="width-100" type="submit"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_addServer') ?></button>
					</form>
					<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_serverList') ?></div>
					<?php if (count($servers) > 0) : ?>
						<div class="adm_online_servers">
							<?php foreach ($servers as $server) : ?>
								<div class="adm_online_server">
									<div class="srv_left">
										<span>ID: <?= $server['id'] ?> | <?= htmlentities($server['name']) ?></span>
										<span><?= $Translate->get_translate_module_phrase('module_page_admintime', '_iksServerID') ?>: <?= $server['server_id'] ?></span>
									</div>
									<div class="srv_right">
										<button server-id="<?= $server['id'] ?>" class="button-delete width-100 deleteServer"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
									</div>
								</div>
							<?php endforeach ?>
						</div>
					<?php else : ?>
						<div class="havent_servers"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_noServers') ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-6">
		<div class="card height-100">
			<div class="card-header">
				<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_accessPage') ?></div>
			</div>
			<div class="card-container">
				<div class="accesses">
					<form class="online_settings" id="addSettings" method="POST">
						<div class="inputs-inline" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_admintime', '_addAccessIksSystem') ?>" data-tippy-placement="bottom">
							<input class="switch" type="checkbox" id="autoaddaccess" name="auto_add_access" <?php ($settings['auto_add_access'] == 1) && print 'checked'; ?>>
							<label for="autoaddaccess"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_autoAccess') ?></label>
						</div>
						<div class="inputs-inline">
							<label for="cleanGroups"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_cleanGroup') ?></label>
							<input type="text" id="cleanGroups" name="clean_groups" value="<?= htmlentities($settings['clean_groups']) ?>">
						</div>
						<button type="submit" class="width-100"><?= $Translate->get_translate_phrase('_saveSettings') ?></button>
					</form>
					<hr>
					<form class="online_settings" id="addAccess" method="POST">
						<div class="inputs-inline">
							<label for="addadminonline"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_addAccess') ?></label>
							<input id="addadminonline" type="text" name="steamid" placeholder="76562398912346679">
						</div>
						<button type="submit" class="width-100"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_addAccessPage') ?></button>
					</form>
					<div class="badge"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_usersWithAccess') ?></div>
					<div class="onlineadm_access_list">
						<?php if (!empty($accesses)) : ?>
							<?php foreach ($accesses as $access) : ?>
								<div class="onlineadm_access_admin">
									<div class="admin_details">
										<img id="avatar" avatarid="<?= $access ?>" src="<?= $General->getAvatar($access, 3) ?>" alt="">
										<div>
											<a href="https:<?= $General->arr_general['site'] ?>profiles/<?= $access ?>/?search=1" target="_blank"><?= $General->checkName($access) ?></a>
										</div>
									</div>
									<button userId="<?= $access ?>" class="button button-delete deleteAccess"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
								</div>
							<?php endforeach; ?>
						<?php else : ?>
							<div class="havent_accesses"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_NOT_ACCESS') ?></div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>