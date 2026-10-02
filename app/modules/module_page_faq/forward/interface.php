<div class="row">
	<div class="col-md-12">
		<div class="card height-100" style="background-color: transparent">
			<div class="acordeon-container">
				<div class="accordion">
					<span class="accordion__heading">
						<svg><use href="/resources/img/sprite.svg#question"></svg>
						<h1><?= $Translate->get_translate_module_phrase('module_page_faq', '_faq') ?></h1>
						<?php if (isset($_SESSION['user_admin'])) : ?>
							<button data-openmodal="createFaq"><?= $Translate->get_translate_module_phrase('module_page_faq', '_createFaq') ?></button>
						<?php endif; ?>
					</span>
					<?php if($cache) : ?>
						<?php foreach($cache as $id => $key) : ?>
							<details class="accordion__details" name="faq">
								<summary class="accordion__summary">
									<span class="accordion__title" role="term" aria-details="faq-<?= $id ?>"><?= $key['title'] ?></span>
									<div class="accordion__summary-icon"><svg><use href="/resources/img/sprite.svg#chevron-down"></svg></div>
									<div class="accordion__buttons">
										<?php if (isset($_SESSION['user_admin'])) : ?>
											<button class="accordion__buttons-action edit" data-id="<?= $id ?>" data-openmodal="editFaq"><svg><use href="/resources/img/sprite.svg#edit-pen"></svg></button>
											<button class="accordion__buttons-action delete" data-id="<?= $id ?>"><svg><use href="/resources/img/sprite.svg#x"></svg></button>
										<?php endif; ?>
									</div>
								</summary>
							</details>
							<div class="accordion__content" id="faq-<?= $id ?>" role="definition">
								<div class="accordion__content-body">
									<p><?= $key['text'] ?></p>
								</div>
							</div>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="no-data"><?= $Translate->get_translate_module_phrase('module_page_faq', '_noFaq') ?></div>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</div>

<?php if (isset($_SESSION['user_admin'])) : ?>
	<div class="popup_modal" id="createFaq">
		<div class="popup_modal_content no-close no-scrollbar">
			<div class="popup_modal_head">
				<?= $Translate->get_translate_module_phrase('module_page_faq', '_newFaq') ?>
				<span class="popup_modal_close">
					<svg>
						<use href="/resources/img/sprite.svg#x"></use>
					</svg>
				</span>
			</div>
			<div class="inputs-inline">
				<label for="question"></label>
				<input type="text" id="question" placeholder="<?= $Translate->get_translate_module_phrase('module_page_faq', '_shortFaq') ?>">
			</div>
			<div class="inputs-inline">
				<label for="answer"></label>
				<textarea name="" id="answer" placeholder="<?= $Translate->get_translate_module_phrase('module_page_faq', '_fullAnswer') ?>"></textarea>
			</div>
			<button class="width-100" id="created"><?= $Translate->get_translate_module_phrase('module_page_faq', '_createNewFaq') ?></button>
		</div>
	</div>
	<div class="popup_modal" id="editFaq" data-modal-type="2"></div>
<?php endif; ?>