<div class="col-md-12">
    <div class="card">
        <div class="results__filters">
            <div class="adaptive-select-wrapper">
                <ul class="adaptive-select__dropdown-list" id="serversSelectResults">
                    <li>
                        <label class="adaptive-select__label" for="server-all">
                            <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_page_results', '_allServers') ?></div>
                            <input class="hide-input" id="server-all" type="radio" name="server" checked value="0">
                        </label>
                    </li>
                    <?php if (!$settings['allInOneReport'] ?? false): ?>
                        <?php foreach ($servers as $index => $server): ?>
                            <li>
                                <label class="adaptive-select__label" for="server-<?= $index ?>">
                                    <div class="adaptive-select__label-text"><?= htmlspecialchars($server['name']) ?></div>
                                    <input class="hide-input" id="server-<?= $index ?>" type="radio" name="server" value="<?= $server['id'] ?>">
                                </label>
                            </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
                <div class="adaptive-select" open-select="serversSelectResults">
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
        </div>
    </div>
    <div id="ResultsBlocks"></div>
</div>