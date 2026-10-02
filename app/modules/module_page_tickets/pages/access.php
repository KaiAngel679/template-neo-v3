<h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_settingsAccess') ?></h2>
<div class="tickets__accesses">
    <div class="tickets__accesses-block">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_addingAccess') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="inputs-inline">
                    <label for="adminSteam">STEAMID</label>
                    <input type="text" id="adminSteam" placeholder="STEAMID 64" required>
                </div>
                <hr>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="reviewTickedAccess" disabled checked>
                    <label for="reviewTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessReview') ?></label>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="deleteTickedAccess">
                    <label for="deleteTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessDelete') ?></label>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="banTickedAccess">
                    <label for="banTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessBlock') ?></label>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="adminsTickedAccess">
                    <label for="adminsTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessAdmins') ?></label>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="settingsTickedAccess">
                    <label for="settingsTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessSettings') ?></label>
                </div>
                <div class="inputs-inline">
                    <input type="checkbox" class="switch" id="generalTickedAccess">
                    <label for="generalTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_immunDelete') ?></label>
                </div>
                <div class="inputs-inline" >
                    <input type="checkbox" class="switch" id="categorysTickedAccess">
                    <label for="categorysTickedAccess"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_giveAccessCat') ?></label>
                </div>
                <div class="adaptive-select-wrapper" style="display: none;" id="categorysTickedAccessInput">
                    <ul class="adaptive-select__dropdown-list" id="categories">
                        <?php foreach($ds->getCategoriesTabs() as $key) : ?>
                            <li>
                                <label class="adaptive-select__label" for="category-<?= $key['id'] ?>">
                                    <div class="adaptive-select__label-text"><?= action_text_clear($key['title']) ?></div>
                                    <input class="hide-input" id="category-<?= $key['id'] ?>" value="<?= $key['id'] ?>" type="checkbox" name="categories-add">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="categories">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#list"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_selectCategories') ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <hr>
                <button class="width-100" id="send-form"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessAdd') ?></button>
            </div>
        </div>
    </div>
    <div class="tickets__accesses-block">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_adminsList') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align:left">
                                    <?= $Translate->get_translate_phrase('_Admin') ?>
                                </th>
                                <th style="text-align:left">
                                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_cats') ?>
                                </th>
                                <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessDelete') ?>" data-tippy-placement="top">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#trash"></use>
                                    </svg>
                                </th>
                                <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessBlock') ?>" data-tippy-placement="top">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#block"></use>
                                    </svg>
                                </th>
                                <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessAdmins') ?>" data-tippy-placement="top">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#key"></use>
                                    </svg>
                                </th>
                                <th data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_accessSettings') ?>" data-tippy-placement="top">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#gear"></use>
                                    </svg>
                                </th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($ds->getAccessAll() as $key) : ?>
                                <tr>
                                    <td>
                                        <span class="ticket__accesses-nickname"><a href="/profiles/<?= $key['steamid'] ?>/?search=1"><?= $General->checkName($key['steamid']) ?></a></span>
                                    </td>
                                    <td style="text-align:left" <?= $key['category'] == null ? '' : 'data-tippy-content="' . $ar->getCategoryNameFromId($key['category'], $ds->getCategoriesTabs()) . '" data-tippy-placement="top"' ?>>
                                        <?= $key['category'] == null ? $Translate->get_translate_module_phrase('module_page_tickets', '_all') : $key['category'] ?>
                                    </td>
                                    <td>
                                        <div class="tickets__svg <?= $key['add_delete'] == 1 ? 'check' : 'x' ?>">
                                            <?= $key['add_delete'] == 1 ? '<svg><use href="/resources/img/sprite.svg#check"></use></svg>' : '<svg><use href="/resources/img/sprite.svg#x"></use></svg>' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tickets__svg <?= $key['add_block'] == 1 ? 'check' : 'x' ?>">
                                            <?= $key['add_block'] == 1 ? '<svg><use href="/resources/img/sprite.svg#check"></use></svg>' : '<svg><use href="/resources/img/sprite.svg#x"></use></svg>' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tickets__svg <?= $key['add_access'] == 1 ? 'check' : 'x' ?>">
                                            <?= $key['add_access'] == 1 ? '<svg><use href="/resources/img/sprite.svg#check"></use></svg>' : '<svg><use href="/resources/img/sprite.svg#x"></use></svg>' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="tickets__svg <?= $key['add_settings'] == 1 ? 'check' : 'x' ?>">
                                            <?= $key['add_settings'] == 1 ? '<svg><use href="/resources/img/sprite.svg#check"></use></svg>' : '<svg><use href="/resources/img/sprite.svg#x"></use></svg>' ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <?php if ($key['steamid'] != $_SESSION['steamid64'] && ($access['general'] == 1 || !isset($key['general']) || $key['general'] != 1)) : ?>
                                                <button class="edit" data-openmodal="editAccesses" data-steamid="<?= $key['steamid'] ?>"><?= $Translate->get_translate_phrase('_Change') ?></button>
                                                <button class="button-delete delete" data-steamid="<?= $key['steamid'] ?>"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="popup_modal" id="editAccesses"></div>