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
						onclick="window.location.href=this.value" checked>
				</label>
			</li>
			<li>
				<label class="adaptive-select__label" for="logslk">
					<div class="adaptive-select__label-text">
						<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logs_pay'); ?>
					</div>
					<input class="hide-input" id="logslk" type="radio"
						value="<?= $General->arr_general['site'] ?>adminpanel/?section=logslk"
						onclick="window.location.href=this.value">
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
			<?php $hasLogs = false;
			foreach (scandir(__DIR__ . '/../../../logs/') as $key) : if ($key != '.' && $key != '..') : if (is_file(__DIR__ . '/../../../logs/' . $key)) : $hasLogs = true;
					endif;
				endif;
			endforeach;
			if ($hasLogs) : ?>
				<form id="all_del_logs">
					<button class="button-delete width-100"><?= $Translate->get_translate_module_phrase('module_page_pay', '_Clear'); ?></button>
				</form>
			<?php endif; ?>
		</div>
		<div class="card-container">
			<div class="lk_logs_wrap">
			<?php $files = scandir(__DIR__ . '/../../../logs/'); $files = array_diff($files, array('.', '..')); $files = array_reverse($files);
                foreach ($files as $key) : if (is_file(__DIR__ . '/../../../logs/' . $key)) : ?>
				<div style="display: flex; justify-content: space-between; gap: .3rem">
					<a class="button" href="<?= set_url_section(get_url(2), 'log', urlencode($key)) ?>"><?= $key ?></a>
					<button class="button-delete" id="log_del" id_del="<?= $key ?>">
						<svg><use href="/resources/img/sprite.svg#broom"></use></svg>
					</button>
				</div>
				<?php endif; endforeach; ?>
			</div>
		</div>
	</div>
</div>
<?php if (!empty($_GET['log'])) : ?>
	<div class="col-md-9">
		<div class="card">
			<div class="card-header">
				<h5 class="badge"><?= $Translate->get_translate_module_phrase('module_page_pay', '_LogContent'); ?>
					<button class="absolut copy-btn" data-clipboard-text="<?= file_get_contents(__DIR__ . '/../../../logs/' . $_GET['log']) ?>">
						<svg><use href="/resources/img/sprite.svg#copy"></use></svg>
						<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_Logs_copylog'); ?>
					</button>
				</h5>
			</div>
			<div class="card-container">
				<div class="log_text">
					<?php if (file_exists(__DIR__ . '/../../../logs/' . $_GET['log'])) : ?>
						<p class="code"><?= file_get_contents(__DIR__ . '/../../../logs/' . $_GET['log']) ?></p>
					<?php else : echo $Translate->get_translate_module_phrase('module_page_adminpanel', '_fileNotFound');
					endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif; ?>