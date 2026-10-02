<script src="/app/templates/neo_remastered/assets/js/tabs.js" async></script>
<link rel="stylesheet" href="/app/templates/neo_remastered/assets/css/github-dark.min.css">
<script src="/app/templates/neo_remastered/assets/js/highlight.min.js"></script>
<div class="tabs tabs--adminpanel">
    <div class="tabs__buttons navigation-filters" role="tablist" aria-labelledby="tablist-1">
        <button class="filter" id="tab-1" type="button" role="tab" aria-selected="true" aria-controls="tabpanel-1">
            <span>SVG icons</span>
        </button>
        <button class="filter" id="tab-2" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-2" tabindex="-1">
            <span>Field components</span>
        </button>
        <button class="filter" id="tab-3" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-3" tabindex="-1">
            <span>Selects</span>
        </button>
        <button class="filter" id="tab-4" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-4" tabindex="-1">
            <span>Buttons</span>
        </button>
        <button class="filter" id="tab-5" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-5" tabindex="-1">
            <span>Modals & dialog</span>
        </button>
        <button class="filter" id="tab-6" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-6" tabindex="-1">
            <span>Skeleton</span>
        </button>
        <button class="filter" id="tab-7" type="button" role="tab" aria-selected="false" aria-controls="tabpanel-7" tabindex="-1">
            <span>Nested list</span>
        </button>
    </div>
    <div id="tabpanel-1" role="tabpanel" tabindex="0" aria-labelledby="tab-1">
        <div class="inputs-inline dev__search">
            <input type="search" id="searchIcon" placeholder="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_searchIcon') ?>">
        </div>
        <div class="dev__icons-wrapper">
            <?php foreach ($Admin->getSprites() as $iconId): ?>
                <div class="dev__icons-icon copy-btn" data-clipboard-text='<svg><use href="/resources/img/sprite.svg#<?= $iconId ?>"></use></svg>' data-tippy-content="<?= $iconId ?>" data-tippy-placement="top">
                    <svg>
                        <use href="/resources/img/sprite.svg#<?= $iconId ?>"></use>
                    </svg>
                    <div class="dev__icons-copy">
                        <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_takeIcon') ?>
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div id="tabpanel-2" role="tabpanel" tabindex="0" aria-labelledby="tab-2" class="is-hidden">
        <div class="dev__fields-section">
            <h2><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_inputs') ?></h2>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_textInput') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="textInput">Your label</label>
                    <input id="textInput" type="text" value="" name="" placeholder="Enter text here" required />
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="textInput">Your label</label>
                            <input id="textInput" type="text" value="" name="" placeholder="Enter text here" />
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_passInput') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="passwordInput1">Your label</label>
                    <div class="number">
                        <input id="passwordInput1" type="password" value="qwerty123" name="">
                        <div class="eye-password" id="show_pass">
                            <svg>
                                <use href="/resources/img/sprite.svg#eye"></use>
                            </svg>
                        </div>
                    </div>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="passwordInput">Your label</label>
                            <div class="number">
                                <input type="password" value="qwerty123" name="" id="passwordInput">
                                <div class="eye-password" id="show_pass">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#eye"></use>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_NumInput') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="numberInput1">Your label</label>
                    <div class="number" id="numberControl">
                        <button class="number-minus" type="button">-</button>
                        <input id="numberInput1" type="number" min="0" value="140">
                        <button class="number-plus" type="button">+</button>
                    </div>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="numberInput">Your label</label>
                            <div class="number" id="numberControl">
                                <button class="number-minus" type="button">-</button>
                                <input id="numberInput" type="number" min="0" value="140">
                                <button class="number-plus" type="button">+</button>
                            </div>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_FileInput') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <div class="file-upload-container">
                        <input type="file" id="fileInput" class="custom-file-input" accept="image/*" name="file">
                        <label for="fileInput">Your label</label>
                        <div id="file-info" class="file-upload-info file-info" style="display: block">File</div>
                    </div>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <div class="file-upload-container">
                                <input type="file" id="fileInput" class="custom-file-input" accept="image/*" name="file">
                                <label for="fileInput">Your label</label>
                                <div id="file-info" class="file-upload-info file-info" style="display: block">File</div>
                            </div>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_dateInput') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="inputDate">Date</label>
                    <input id="inputDate" type="date" />
                </div>
                <pre class="dev__fields-pre dev_mb">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="inputDate">Date</label>
                            <input id="inputDate" type="date" />
                        </div>
                    </code>
                </pre>
                <div class="inputs-inline">
                    <label for="inputTime">Time</label>
                    <input id="inputTime" type="time" />
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="inputTime">Time</label>
                            <input id="inputTime" type="time" />
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_checkbox') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <input type="checkbox" id="checkboxInput">
                    <label for="checkboxInput">Default checkbox example</label>
                </div>
                <pre class="dev__fields-pre dev_mb">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <input type="checkbox" id="checkboxInput">
                            <label for="checkboxInput">Default checkbox example</label>
                        </div>
                    </code>
                </pre>
                <div class="inputs-inline">
                    <input type="checkbox" id="checkboxInput2" class="switch">
                    <label for="checkboxInput2">Switch checkbox example (modern)</label>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <input type="checkbox" id="checkboxInput2" class="switch">
                            <label for="checkboxInput2">Switch checkbox example (modern)</label>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_fieldGroups') ?></h3>
                <hr>
                <fieldset>
                    <legend>Your legend</legend>
                    <div class="inputs-inline">
                        <input type="checkbox" id="checkboxFieldset" name="fieldsetElements">
                        <label for="checkboxFieldset">Default checkbox example</label>
                    </div>
                    <div class="inputs-inline">
                        <input type="checkbox" id="checkboxFieldset2" class="switch" name="fieldsetElements">
                        <label for="checkboxFieldset2">Switch checkbox example</label>
                    </div>
                    <div class="inputs-inline">
                        <input type="radio" id="radioFieldset" name="fieldsetElements">
                        <label for="radioFieldset">Radio 1 example</label>
                    </div>
                    <div class="inputs-inline">
                        <input type="radio" id="radioFieldset2" name="fieldsetElements">
                        <label for="radioFieldset2">Radio 2 example</label>
                    </div>
                </fieldset>
                <pre class="dev__fields-pre dev_mb">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <fieldset>
                            <legend>Your legend</legend>
                            <div class="inputs-inline">
                                <input type="checkbox" id="checkboxFieldset" name="fieldsetElements">
                                <label for="checkboxFieldset">Default checkbox example</label>
                            </div>
                            <div class="inputs-inline">
                                <input type="checkbox" id="checkboxFieldset2" class="switch" name="fieldsetElements">
                                <label for="checkboxFieldset2">Switch checkbox example</label>
                            </div>
                            <div class="inputs-inline">
                                <input type="radio" id="radioFieldset" name="fieldsetElements">
                                <label for="radioFieldset">Radio 1 example</label>
                            </div>
                            <div class="inputs-inline">
                                <input type="radio" id="radioFieldset2" name="fieldsetElements">
                                <label for="radioFieldset2">Radio 2 example</label>
                            </div>
                        </fieldset>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_textarea') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="textarea">Your label</label>
                    <textarea id="textarea" rows="4" placeholder="Tape deasription" autofocus="" required="" maxlength="1000" spellcheck="true" wrap="hard"></textarea>
                </div>
                <pre class="dev__fields-pre dev_mb">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="textarea">Textarea</label>
                            <textarea id="textarea" rows="4" placeholder="Tape deasription" autofocus="" required="" maxlength="1000" spellcheck="true" wrap="hard"></textarea>
                        </div>
                    </code>
                </pre>
            </div>
        </div>
    </div>

    <div id="tabpanel-3" role="tabpanel" tabindex="0" aria-labelledby="tab-3" class="is-hidden">
        <div class="dev__fields-section">
            <h2><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_selects') ?></h2>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_multiSelects') ?></h3>
                <hr>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="id-select">
                        <li>
                            <label class="adaptive-select__label" for="1">
                                <div class="adaptive-select__label-text">Option text 1</div>
                                <input class="hide-input" id="1" type="checkbox" name="district">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="2">
                                <div class="adaptive-select__label-text">Option text 2</div>
                                <input class="hide-input" id="2" type="checkbox" name="district">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="id-select">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#servers"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">Select multiple</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="id-select">
                                <li>
                                    <label class="adaptive-select__label" for="1">
                                        <div class="adaptive-select__label-text">Option text 1</div>
                                        <input class="hide-input" id="1" type="checkbox" name="district">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="2">
                                        <div class="adaptive-select__label-text">Option text 2</div>
                                        <input class="hide-input" id="2" type="checkbox" name="district">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="id-select">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#servers"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text">Select multiple</span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_singleSelects') ?></h3>
                <hr>
                <div class="adaptive-select-wrapper">
                    <ul class="adaptive-select__dropdown-list" id="id-select2">
                        <li>
                            <label class="adaptive-select__label" for="11">
                                <div class="adaptive-select__label-text">option text 1</div>
                                <input class="hide-input" id="11" type="radio" name="district">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="22">
                                <div class="adaptive-select__label-text">option text 2</div>
                                <input class="hide-input" id="22" type="radio" name="district">
                            </label>
                        </li>
                        <li>
                            <label class="adaptive-select__label" for="33">
                                <div class="adaptive-select__label-text">option text 3</div>
                                <input class="hide-input" id="33" type="radio" name="district">
                            </label>
                        </li>
                    </ul>
                    <div class="adaptive-select" open-select="id-select2">
                        <span class="adaptive-select__fist-icon">
                            <svg>
                                <use href="/resources/img/sprite.svg#heart"></use>
                            </svg>
                        </span>
                        <span class="adaptive-select__span_text">Select single</span>
                        <span class="margin-left-auto adaptive-select__arrow">
                            <svg>
                                <use href="/resources/img/sprite.svg#chevron-down"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="adaptive-select-wrapper">
                            <ul class="adaptive-select__dropdown-list" id="id-select2">
                                <li>
                                    <label class="adaptive-select__label" for="11">
                                        <div class="adaptive-select__label-text">option text 1</div>
                                        <input class="hide-input" id="11" type="radio" name="district">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="22">
                                        <div class="adaptive-select__label-text">option text 2</div>
                                        <input class="hide-input" id="22" type="radio" name="district">
                                    </label>
                                </li>
                                <li>
                                    <label class="adaptive-select__label" for="33">
                                        <div class="adaptive-select__label-text">option text 3</div>
                                        <input class="hide-input" id="33" type="radio" name="district">
                                    </label>
                                </li>
                            </ul>
                            <div class="adaptive-select" open-select="id-select2">
                                <span class="adaptive-select__fist-icon">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#heart"></use>
                                    </svg>
                                </span>
                                <span class="adaptive-select__span_text">Select single</span>
                                <span class="margin-left-auto adaptive-select__arrow">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#chevron-down"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>
                    </code>
                </pre>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_standartSelects') ?></h3>
                <hr>
                <div class="inputs-inline">
                    <label for="defaultSelect">Your label</label>
                    <select name="speed" id="defaultSelect">
                        <option value="1" selected>Option 1 selected</option>
                        <option value="2">Option 2</option>
                        <option value="3">Option 3</option>
                    </select>
                </div>
                <pre class="dev__fields-pre">
                    <svg>
                        <use href="/resources/img/sprite.svg#copy-list"></use>
                    </svg>
                    <code class="language-html">
                        <div class="inputs-inline">
                            <label for="defaultSelect">Your label</label>
                            <select name="speed" id="defaultSelect">
                                <option value="1" selected>Option 1 selected</option>
                                <option value="2">Option 2</option>
                                <option value="3">Option 3</option>
                            </select>
                        </div>
                    </code>
                </pre>
            </div>
        </div>
    </div>
    <div id="tabpanel-4" role="tabpanel" tabindex="0" aria-labelledby="tab-4" class="is-hidden">
        <div class="dev__fields-section">
            <h2><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_buttons') ?></h2>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_standartButtons') ?></h3>
                <hr>
                <div class="dev__example-elements">
                    <button>Default button</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button>Default button</button>
                        </code>
                    </pre>
                    <button><svg>
                            <use href="/resources/img/sprite.svg#star-fill"></use>
                        </svg>Default button + icon</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button><svg><use href="/resources/img/sprite.svg#star-fill"></use></svg>Default button + icon</button>
                        </code>
                    </pre>
                    <button disabled>Disabled default button</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button disabled>Disabled default button</button>
                        </code>
                    </pre>
                </div>

            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_specialButtons') ?></h3>
                <hr>
                <div class="dev__example-elements">
                    <button class="active">Active button</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="active">Active button</button>
                        </code>
                    </pre>
                    <button class="pay-link">Money button</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="pay-link">Money button</button>
                        </code>
                    </pre>
                    <button class="button-delete"><svg>
                            <use href="/resources/img/sprite.svg#trash"></use>
                        </svg>Delete button</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="button-delete"><svg><use href="/resources/img/sprite.svg#trash"></use></svg>Delete button</button>
                        </code>
                    </pre>
                </div>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_filterButtons') ?></h3>
                <hr>
                <div class="dev__example-elements">
                    <button class="filter">Filter 1</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="filter">Filter 1</button>
                        </code>
                    </pre>
                    <button class="filter active">Filter 1 active</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="filter active">Filter 1 active</button>
                        </code>
                    </pre>
                    <button class="filter"><svg>
                            <use href="/resources/img/sprite.svg#filters"></use>
                        </svg>Filter 2 + icon</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="filter"><svg><use href="/resources/img/sprite.svg#filters"></use></svg>Filter 2 + icon</button>
                        </code>
                    </pre>
                    <button class="filter active"><svg>
                            <use href="/resources/img/sprite.svg#filters"></use>
                        </svg>Filter 2 active + icon</button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="filter active"><svg><use href="/resources/img/sprite.svg#filters"></use></svg>Filter 2 active + icon</button>
                        </code>
                    </pre>
                </div>
            </div>
            <div class="dev__example-block">
                <h3><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_iconsButtons') ?></h3>
                <hr>
                <div class="dev__example-elements">
                    <button class="button-icon"><svg>
                            <use href="/resources/img/sprite.svg#steam"></use>
                        </svg></button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="button-icon"><svg><use href="/resources/img/sprite.svg#steam"></use></svg></button>
                        </code>
                    </pre>
                    <button class="button-icon active"><svg>
                            <use href="/resources/img/sprite.svg#steam"></use>
                        </svg></button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="button-icon active"><svg><use href="/resources/img/sprite.svg#steam"></use></svg></button>
                        </code>
                    </pre>
                    <button class="button-icon" disabled><svg>
                            <use href="/resources/img/sprite.svg#steam"></use>
                        </svg></button>
                    <pre class="dev__fields-pre">
                        <svg>
                            <use href="/resources/img/sprite.svg#copy-list"></use>
                        </svg>
                        <code class="language-html">
                            <button class="button-icon" disabled><svg><use href="/resources/img/sprite.svg#steam"></use></svg></button>
                        </code>
                    </pre>
                </div>
            </div>
        </div>
    </div>
    <div id="tabpanel-5" role="tabpanel" tabindex="0" aria-labelledby="tab-5" class="is-hidden">
        <div class="dev__fields-section">
            <div class="dev__fields-section">
                <h2><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_modal') ?></h2>
                <div class="dev__example-block">
                    <div class="dev__example-elements">
                        <button data-openmodal="keyOpenModal">Open modal window</button>
                        <div class="popup_modal" id="keyOpenModal">
                            <div class="popup_modal_content no-close no-scrollbar">
                                <div class="popup_modal_head">
                                    Modal title
                                    <span class="popup_modal_close">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg>
                                    </span>
                                </div>
                                <div>
                                    <p>This is a modal window example content.</p>
                                    <p>You can put any HTML content here, such as text, images, forms, etc.</p>
                                </div>
                            </div>
                        </div>
                        <pre class="dev__fields-pre">
                            <svg>
                                <use href="/resources/img/sprite.svg#copy-list"></use>
                            </svg>
                            <code class="language-html">
                                <button data-openmodal="keyOpenModal">Open modal window</button>
                                <div class="popup_modal" id="keyOpenModal">
                                    <div class="popup_modal_content no-close no-scrollbar">
                                        <div class="popup_modal_head">
                                            Modal title
                                            <span class="popup_modal_close">
                                                <svg>
                                                    <use href="/resources/img/sprite.svg#x"></use>
                                                </svg>
                                            </span>
                                        </div>
                                        <div>
                                            <p>This is a modal window example content.</p>
                                            <p>You can put any HTML content here, such as text, images, forms, etc.</p>
                                        </div>
                                    </div>
                                </div>
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tabpanel-6" role="tabpanel" tabindex="0" aria-labelledby="tab-6" class="is-hidden">
        <div class="dev__fields-section">
            <div class="dev__fields-section">
                <h2>Скелетоны</h2>
                <div class="dev__example-block">
                    <div class="dev__example-elements">
                        <label for="">Skeleton default → <code style="width:max-content">class="skeleton--default"</code></label>
                        <button class="example-button skeleton--default"><svg>
                                <use href="/resources/img/sprite.svg#bolt"></use>
                            </svg> Skeleton button</button>
                        <div class="example-block skeleton--default">Text</div>
                        <pre class="dev__fields-pre">
                            <svg>
                                <use href="/resources/img/sprite.svg#copy-list"></use>
                            </svg>
                            <code class="language-html">
                                <button class="example-button skeleton--default"><svg><use href="/resources/img/sprite.svg#bolt"></use></svg> Skeleton button</button>
                                <div class="example-block skeleton--default">Text</div>
                            </code>
                        </pre>
                        <label for="">Skeleton slow → <code style="width:max-content">class="skeleton--slow"</code></label>
                        <button class="example-button skeleton--slow">Skeleton button</button>
                        <div class="example-block skeleton--slow">Text</div>
                        <pre class="dev__fields-pre">
                            <svg>
                                <use href="/resources/img/sprite.svg#copy-list"></use>
                            </svg>
                            <code class="language-html">
                                <button class="example-button skeleton--slow">Skeleton button</button>
                                <div class="example-block skeleton--slow">Text</div>
                            </code>
                        </pre>
                        <label for="">Skeleton fast → <code style="width:max-content">class="skeleton--fast"</code></label>
                        <button class="example-button skeleton--fast">Skeleton button</button>
                        <div class="example-block skeleton--fast">Text</div>
                        <pre class="dev__fields-pre">
                            <svg>
                                <use href="/resources/img/sprite.svg#copy-list"></use>
                            </svg>
                            <code class="language-html">
                                <button class="example-button skeleton--fast">Skeleton button</button>
                                <div class="example-block skeleton--fast">Text</div>
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="tabpanel-7" role="tabpanel" tabindex="0" aria-labelledby="tab-7" class="is-hidden">
        <div class="dev__fields-section">
            <div class="dev__fields-section">
                <h2>Вложенные списки</h2>
                <div class="dev__example-block">
                    <div class="dev__example-elements">
                        <div class="list-group-item nested-1">
                            <span class="item-title">
                                <svg>
                                    <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                </svg>
                                Category (Item 1)
                            </span>
                            <div class="list-group nested-sort">
                                <span class="item-title">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                    </svg>
                                    Sub category (Item 2)
                                </span>
                                <div class="list-group-item nested-2">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                    </svg>
                                    Item 3.1
                                </div>
                                <div class="list-group-item nested-2">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                    </svg>
                                    Item 3.2
                                </div>
                                <div class="list-group-item nested-2">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                    </svg>
                                    Item 3.3
                                </div>
                                <div class="list-group-item nested-2">
                                    <svg>
                                        <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                    </svg>
                                    Item 3.4
                                </div>
                            </div>
                            <div class="list-group-item nested-2">
                                <svg>
                                    <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                </svg>
                                Item 3.5
                            </div>
                            <div class="list-group-item nested-2">
                                <svg>
                                    <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                </svg>
                                Item 3.6
                            </div>
                        </div>
                        <pre class="dev__fields-pre">
                            <svg>
                                <use href="/resources/img/sprite.svg#copy-list"></use>
                            </svg>
                            <code class="language-html">
                                <div class="list-group-item nested-1">
                                    <span class="item-title">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                        </svg>
                                        Category (Item 1)
                                    </span>
                                    <div class="list-group nested-sort">
                                        <span class="item-title">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            Sub category (Item 2)
                                        </span>
                                        <div class="list-group-item nested-2">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            Item 3.1
                                        </div>
                                        <div class="list-group-item nested-2">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            Item 3.2
                                        </div>
                                        <div class="list-group-item nested-2">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            Item 3.3
                                        </div>
                                        <div class="list-group-item nested-2">
                                            <svg>
                                                <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                            </svg>
                                            Item 3.4
                                        </div>
                                    </div>
                                    <div class="list-group-item nested-2">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                        </svg>
                                        Item 3.5
                                    </div>
                                    <div class="list-group-item nested-2">
                                        <svg>
                                            <use href="/resources/img/sprite.svg#arrow-move-updown"></use>
                                        </svg>
                                        Item 3.6
                                    </div>
                                </div>
                            </code>
                        </pre>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('pre code').forEach(function(block) {
        var raw = block.querySelector('*') ? block.innerHTML : (block.textContent || '');
        raw = raw.replace(/^[\s\r\n]*\n/, '').replace(/\n[\s\r\n]*$/, '');
        var lines = raw.split('\n');
        var minIndent = null;
        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            if (line.trim().length === 0) continue;
            var m = line.match(/^[ \t]*/);
            var indent = m ? m[0].length : 0;
            if (minIndent === null || indent < minIndent) minIndent = indent;
        }
        if (minIndent && minIndent > 0) {
            var stripRe = new RegExp('^[ \t]{' + minIndent + '}');
            lines = lines.map(function(l) {
                return l.replace(stripRe, '');
            });
        }

        block.textContent = lines.join('\n');

        var pre = block.closest('pre.dev__fields-pre');
        if (pre) {
            var icon = pre.querySelector('svg');
            if (icon) {
                icon.classList.add('copy-btn');
                icon.setAttribute('data-clipboard-text', block.textContent);
            }
        }
    });

    hljs.highlightAll();
</script>