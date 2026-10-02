<div class="col-md-12">
    <div class="card">
        <div class="results__filters">
            <button class="active" data-openmodal="addAdmin">
                <svg>
                    <use href="/resources/img/sprite.svg#new-users"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_results', '_addNewAdmin') ?>
            </button>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="serversSelectAdmins">
                    <li>
                        <label class="adaptive-select__label" for="server-all">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_allServers') ?></div>
                            <input class="hide-input" id="server-all" type="radio" name="server" value="0" checked>
                        </label>
                    </li>
                    <?php foreach ($servers as $index => $server): ?>
                        <li>
                            <label class="adaptive-select__label" for="server-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $server['name'] ?></div>
                                <input class="hide-input" id="server-<?= $index ?>" type="radio" name="server" value="<?= $server['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="serversSelectAdmins">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectServer') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="serversGroupAdmins">
                    <li>
                        <label class="adaptive-select__label" for="group-all">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_allGroups') ?></div>
                            <input class="hide-input" id="group-all" type="radio" name="group" value="0" checked>
                        </label>
                    </li>
                    <?php foreach ($groups as $index => $group): ?>
                        <li>
                            <label class="adaptive-select__label" for="group-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                <input class="hide-input" id="group-<?= $index ?>" type="radio" name="group" value="<?= $group['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="serversGroupAdmins">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectGroup') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <button class="active ml-auto" data-openmodal="importAdmins">
                <svg>
                    <use href="/resources/img/sprite.svg#new-users"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_results', '_importAdmins') ?>
            </button>
        </div>
    </div>
    <div class="ai__admins">
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 12rem;"><?= $Translate->get_translate_module_phrase('module_page_results', '_nickname') ?></th>
                            <th>Discord</th>
                            <th>Telegram</th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_position') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_server') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_dateOfAdoption') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_results', '_acceptedBy') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="AdminsTableBody">
                    </tbody>
                </table>
            </div>
        </div>
        <div id="Pagination"></div>
    </div>
</div>

<div class="popup_modal" id="addAdmin">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_addmingNewAdmin') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form class="ai__modal" id="addAdminForm">
            <label for="adminAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_positionOnProject') ?></label>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="AddGroup">
                    <?php foreach ($groups as $index => $group): ?>
                        <li>
                            <label class="adaptive-select__label" for="addgroup-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                <input class="hide-input" id="addgroup-<?= $index ?>" type="radio" name="group" value="<?= $group['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="AddGroup">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_position') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="serversSelectAdd">
                    <?php foreach ($servers as $index => $server): ?>
                        <li>
                            <label class="adaptive-select__label" for="serveradd-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $server['name'] ?></div>
                                <input class="hide-input" id="serveradd-<?= $index ?>" type="checkbox" name="server[]" value="<?= $server['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="serversSelectAdd">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectServer') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="adminSteam"><?= $Translate->get_translate_module_phrase('module_page_results', '_steamAccount') ?></label>
                <input id="adminSteam" type="text" value="" name="steam" placeholder="https://steamcommunity.com/profiles/76561198995346679" required>
            </div>
            <div class="inputs-inline">
                <label for="adminTg">Telegram</label>
                <input id="adminTg" type="text" value="" name="telegram" placeholder="@username">
            </div>
            <div class="inputs-inline">
                <label for="adminDs">Discord ID</label>
                <input id="adminDs" type="text" value="" name="discord" placeholder="@username">
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_addAdmin') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="editAdmin">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_editingAdmin') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form class="ai__modal" id="editAdminForm">
            <label for="adminAccessEdit"><?= $Translate->get_translate_module_phrase('module_page_results', '_positionOnProject') ?></label>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="EditGroup">
                    <?php foreach ($groups as $index => $group): ?>
                        <li>
                            <label class="adaptive-select__label" for="editgroup-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                <input class="hide-input" id="editgroup-<?= $index ?>" type="radio" name="group" value="<?= $group['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="EditGroup">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_position') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="serversSelectEdit">

                    <?php foreach ($servers as $index => $server): ?>
                        <li>
                            <label class="adaptive-select__label" for="serveredit-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $server['name'] ?></div>
                                <input class="hide-input" id="serveredit-<?= $index ?>" type="checkbox" name="server[]" value="<?= $server['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="serversSelectEdit">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_selectServer') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="adminSteamEdit"><?= $Translate->get_translate_module_phrase('module_page_results', '_steamAccount') ?></label>
                <input id="adminSteamEdit" type="text" value="" name="steam" placeholder="https://steamcommunity.com/profiles/76561198995346679" required>
            </div>
            <div class="inputs-inline">
                <label for="adminTgEdit">Telegram</label>
                <input id="adminTgEdit" type="text" value="" name="telegram" placeholder="@username">
            </div>
            <div class="inputs-inline">
                <label for="adminDsEdit">Discord ID</label>
                <input id="adminDsEdit" type="text" value="" name="discord" placeholder="@username">
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_saveChanges') ?></button>
        </form>
    </div>
</div>

<div class="popup_modal" id="importAdmins">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_results', '_importAdmins') ?>
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <form class="ai__modal" id="importAdminsForm">
            <label for="adminAccess"><?= $Translate->get_translate_module_phrase('module_page_results', '_skipImportGroups') ?></label>
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="importFiltergroup">
                    <li>
                        <label class="adaptive-select__label" for="importFiltergroup-null">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_none') ?></div>
                            <input class="hide-input" id="importFiltergroup-null" type="checkbox" name="group[]" value="0" checked>
                        </label>
                    </li>
                    <?php foreach ($groups as $index => $group): ?>
                        <li>
                            <label class="adaptive-select__label" for="importFiltergroup-<?= $index ?>">
                                <div class="adaptive-select__label-text"><?= $group['name'] ?></div>
                                <input class="hide-input" id="importFiltergroup-<?= $index ?>" type="checkbox" name="group[]" value="<?= $group['id'] ?>">
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="adaptive-select" open-select="importFiltergroup">
                    <span class="adaptive-select__fist-icon">
                        <svg>
                            <use href="/resources/img/sprite.svg#servers"></use>
                        </svg>
                    </span>
                    <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_page_results', '_position') ?></span>
                    <span class="margin-left-auto adaptive-select__arrow">
                        <svg>
                            <use href="/resources/img/sprite.svg#chevron-down"></use>
                        </svg>
                    </span>
                </div>
            </div>
            <div class="inputs-inline">
                <input type="checkbox" id="ignoreIssetAdmins" class="switch" name="ignoreIssetAdmins">
                <label for="ignoreIssetAdmins"><?= $Translate->get_translate_module_phrase('module_page_results', '_IgnoreIssetAdmins') ?></label>
            </div>
            <button class="width-100"><?= $Translate->get_translate_module_phrase('module_page_results', '_import') ?></button>
        </form>
    </div>
</div>