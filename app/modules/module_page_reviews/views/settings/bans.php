<div class="at__settings-content">
    <h3 class="at__settings-title"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_bansTitle') ?></h3>
    <div class="at__sections-wrapper">
        <div class="at__settings-adding">
            <h4 class="at__settings-subtitle"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banAdd') ?></h4>
            <hr>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_bansHint') ?>
            </div>
            <div class="inputs-inline">
                <label for="rvBanSteam"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banSteam') ?></label>
                <input id="rvBanSteam" type="text" maxlength="32" value="" placeholder="7656119…" autocomplete="off">
            </div>
            <div class="inputs-inline">
                <label for="rvBanIp"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banIp') ?></label>
                <input id="rvBanIp" type="text" maxlength="45" value="" placeholder="0.0.0.0" autocomplete="off">
            </div>
            <div class="inputs-inline">
                <label><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banScope') ?></label>
                <div class="adaptive-select-wrapper" data-change-icon>
                    <ul class="adaptive-select__dropdown-list" id="rvBanScopeList">
                        <?php foreach ($banScopes as $scope): ?>
                            <li>
                                <label class="adaptive-select__label" for="rvBanScope-<?= action_text_clear($scope['key']) ?>">
                                    <span class="adaptive-select__icon">
                                        <svg><use href="/resources/img/sprite.svg#<?= action_text_clear($scope['icon']) ?>"></use></svg>
                                    </span>
                                    <div class="adaptive-select__label-text"><?= action_text_clear($scope['label']) ?></div>
                                    <input
                                        class="hide-input"
                                        id="rvBanScope-<?= action_text_clear($scope['key']) ?>"
                                        type="radio"
                                        name="rv-ban-scope"
                                        value="<?= action_text_clear($scope['key']) ?>"
                                        <?= $scope['key'] === 'all' ? 'checked' : '' ?>
                                    >
                                </label>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="adaptive-select" open-select="rvBanScopeList">
                        <span class="adaptive-select__fist-icon">
                            <svg><use href="/resources/img/sprite.svg#block"></use></svg>
                        </span>
                        <span class="adaptive-select__span_text"><?= action_text_clear($banScopes[0]['label']) ?></span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg><use href="/resources/img/sprite.svg#chevron-down"></use></svg>
                        </span>
                    </div>
                </div>
            </div>
            <div class="inputs-inline">
                <label for="rvBanReason"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banReason') ?></label>
                <input id="rvBanReason" type="text" maxlength="200" value="" placeholder="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banReasonPlaceholder') ?>" autocomplete="off">
            </div>
            <div class="inputs-inline at__fixed-checkbox">
                <input type="checkbox" id="rvBanDeleteContent" class="switch" <?= !empty($settings['ban_delete_content']) ? 'checked' : '' ?>>
                <label for="rvBanDeleteContent" class="at__fixed-label"><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banDeleteContent') ?></label>
            </div>
            <div class="at__settings-info-text">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banDeleteContentHint') ?>
            </div>
            <button type="button" class="at__button-setting active width-100" id="rvBanAdd">
                <?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banCreate') ?>
            </button>
        </div>

        <div class="at__settings-section">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banSteam') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banIp') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banScope') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banReason') ?></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="rvBansTableBody">
                        <?php foreach ($bans as $ban): ?>
                            <tr data-ban-id="<?= action_text_clear((string) ($ban['id'] ?? '')) ?>">
                                <td><?= action_text_clear((string) ($ban['steamid'] ?: '—')) ?></td>
                                <td><?= action_text_clear((string) ($ban['ip'] ?: '—')) ?></td>
                                <td><?= action_text_clear($ban['scope_label']) ?></td>
                                <td><?= action_text_clear((string) ($ban['reason'] ?: '—')) ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <button type="button" class="button-icon button-delete rv-btn-ban-delete" data-tippy-content="<?= $Translate->get_translate_module_phrase('module_page_reviews', '_rv_banDelete') ?>" data-tippy-placement="top">
                                            <svg><use href="/resources/img/sprite.svg#trash"></use></svg>
                                        </button>
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
