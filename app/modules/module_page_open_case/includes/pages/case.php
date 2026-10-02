<?php if (!isset($_SESSION['user_admin']) || IN_LR != true) {
	header('Location: ' . $General->arr_general['site']);
	exit;
}; ?>
<div class="col-md-12">
	<div class="card">
		<div class="card-header">
			<h5 class="badge">
				<?= $Translate->get_translate_module_phrase('module_page_open_case', '_CaseContent') ?>
			</h5>
		</div>
		<div class="card-container">
			<div class="table-responsive">
				<table class="table table-hover">
					<thead>
						<tr>
							<th></th>
							<th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Title') ?></th>
							<th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Content') ?></th>
							<th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Chance') ?></th>
							<th><?= $Translate->get_translate_module_phrase('module_page_open_case', '_SellingPrice') ?></th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($subjects as $key) : ?>
							<tr>
								<td>
									<div class="subject_color live-link-<?= $key['subject_class'] ?>">
										<img src="<?= $General->arr_general['site'] ?><?= $key['subject_img'] ?>">
									</div>
								</td>
								<td><?= $key['subject_name'] ?></td>
								<td><?= $key['subject_content'] ?></td>
								<td><?= $key['subject_chance'] ?></td>
								<td><?= $key['subject_sale'] ?></td>
								<td class="buttons_adm_case">
									<button onclick="CasesAjax('edit_subject_btn', <?= $key['id'] ?>)" class="button icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_phrase('_Change') ?>" data-tippy-placement="top">
										<svg viewBox="0 0 512 512">
											<path d="M471.6 21.7c-21.9-21.9-57.3-21.9-79.2 0L362.3 51.7l97.9 97.9 30.1-30.1c21.9-21.9 21.9-57.3 0-79.2L471.6 21.7zm-299.2 220c-6.1 6.1-10.8 13.6-13.5 21.9l-29.6 88.8c-2.9 8.6-.6 18.1 5.8 24.6s15.9 8.7 24.6 5.8l88.8-29.6c8.2-2.8 15.7-7.4 21.9-13.5L437.7 172.3 339.7 74.3 172.4 241.7zM96 64C43 64 0 107 0 160V416c0 53 43 96 96 96H352c53 0 96-43 96-96V320c0-17.7-14.3-32-32-32s-32 14.3-32 32v96c0 17.7-14.3 32-32 32H96c-17.7 0-32-14.3-32-32V160c0-17.7 14.3-32 32-32h96c17.7 0 32-14.3 32-32s-14.3-32-32-32H96z" />
										</svg>
									</button>
									<button onclick="CasesAjax('del_subject_btn', <?= $key['id'] ?>)" class="button button-delete icon_btn_transparent" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_open_case', '_Case_ClearCaseNow') ?>" data-tippy-placement="top">
										<svg viewBox="0 0 320 512">
											<path d="M310.6 150.6c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L160 210.7 54.6 105.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L114.7 256 9.4 361.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0L160 301.3 265.4 406.6c12.5 12.5 32.8 12.5 45.3 0s12.5-32.8 0-45.3L205.3 256 310.6 150.6z"></path>
										</svg>
									</button>
								</td>
							</tr>
						<?php endforeach ?>
					</tbody>
				</table>
			</div>
			<button class="width-100" style="margin-top: .5rem" onclick="CasesAjax('add_subject_btn')"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_AddItem') ?></button>
		</div>
	</div>
</div>
<?php require MODULES . 'module_page_open_case/includes/modals/subject.php'; ?>
<?php require MODULES . 'module_page_open_case/includes/modals/delete.php'; ?>