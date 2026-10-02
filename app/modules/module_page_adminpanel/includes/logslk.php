<div class="col-md-12">
	<div class="adaptive-select-wrapper">
		<ul class="adaptive-select__dropdown-list" id="option-log-select">
			<li>
				<label class="adaptive-select__label" for="logsweb">
					<div class="adaptive-select__label-text">
						<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logs_engine'); ?>
					</div>
					<input class="hide-input" id="logsweb" type="radio"
						value="<?= $General->arr_general['site'] ?>adminpanel/?section=logsweb"
						onclick="window.location.href=this.value">
				</label>
			</li>
			<li>
				<label class="adaptive-select__label" for="logslk">
					<div class="adaptive-select__label-text">
						<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logs_pay'); ?>
					</div>
					<input class="hide-input" id="logslk" type="radio"
						value="<?= $General->arr_general['site'] ?>adminpanel/?section=logslk"
						onclick="window.location.href=this.value" checked>
				</label>
			</li>
		</ul>
		<div class="adaptive-select" open-select="option-log-select">
			<span
				class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_rangsFormat') ?></span>
			<span class="margin-left-auto adaptive-select__arrow">
				<svg>
					<use href="/resources/img/sprite.svg#chevron-down"></use>
				</svg>
			</span>
		</div>
	</div>
</div>
<div class="col-md-3">
	<div class="card">
		<div class="card-header">
			<h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_LogList'); ?></h5>
			<?php if (!empty($Admin->LkLogs())): ?>
				<form id="all_del_logs_lk">
					<button class="button-delete width-100">
						<?= $Translate->get_translate_module_phrase('module_page_pay', '_Clear'); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
		<div class="card-container">
			<div class="lk_logs_wrap scroll no-scrollbar">
				<?php foreach ($Admin->LkLogs() as $log): ?>
					<div style="display: flex; justify-content: space-between; gap: .3rem">
						<a class="button" href="<?= set_url_section(get_url(2), 'log', $log['log_name']) ?>">
							<?= $log['log_name'] ?>
						</a>
						<button class="button-delete" id="log_del_lk" id_del="<?= $log['log_name'] ?>">
							<svg>
								<use href="/resources/img/sprite.svg#broom"></use>
							</svg>
						</button>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
</div>
<?php if (!empty($_GET['log'])): ?>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_LogContent'); ?>
					<button class="absolut copy-btn"
						data-clipboard-text="<?php foreach ($Admin->LkLogContent($_GET['log']) as $key): ?><?= $key['log_name'] . $key['log_time'] . LangValReplace($Translate->get_translate_module_phrase('module_page_pay', $key['log_content']), json_decode(str_replace('[]', '', $key['log_value']), true)); ?><?php endforeach; ?>">
						<svg>
							<use href="/resources/img/sprite.svg#copy"></use>
						</svg>
						<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logs_copylog'); ?>
					</button>
				</h5>
			</div>
			<div class="card-container no-scrollbar" style="max-height: 48vh; overflow: hidden; overflow-y: scroll;">
				<?php foreach ($Admin->LkLogContent($_GET['log']) as $key): ?>
					<div class="log_text">
						<?= $key['log_name'] . $key['log_time'] . LangValReplace($Translate->get_translate_module_phrase('module_page_pay', $key['log_content']), json_decode(str_replace('[]', '', $key['log_value']), true)); ?>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
	</div>
<?php endif; ?>