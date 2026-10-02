<?php
if (IN_LR != true) {
    header('Location: ' . print $General->arr_general['site']);
    exit;
}

if (isset($_SESSION['user_admin'])) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="admin_nav">
                <button class="secondary_btn <?php get_section('section', 'cases') == 'cases' && print 'active' ?>" onclick="location.href = window.location.pathname;">
                    <svg><use href="/resources/img/sprite.svg#box-empty"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenCase') ?>
                </button>
                <button class="secondary_btn <?php get_section('section', 'cases') == 'admin' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'admin') ?>';">
                    <svg><use href="/resources/img/sprite.svg#gear"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_CaseSettings') ?>
                </button>
                <button class="secondary_btn <?php get_section('section', 'cases') == 'cases_list' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'cases_list') ?>';">
                    <svg><use href="/resources/img/sprite.svg#list"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenCaseList') ?>
                </button>
                <button class="secondary_btn <?php get_section('section', 'cases') == 'wins_list' && print 'active' ?>" onclick="location.href = '<?= set_url_section(get_url(2), 'section', 'wins_list') ?>';">
                    <svg><use href="/resources/img/sprite.svg#star-fill"></use></svg>
                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_WinsList') ?>
                </button>
            </div>
        </div>
    </div>
<?php endif;
if (isset($_GET['section'])) : ?>
    <div class="row">
        <?php switch ($_GET['section']) {
            case  'admin':
                require MODULES . 'module_page_open_case' . '/includes/pages/admin.php';
                break;
            case  'case':
                require MODULES . 'module_page_open_case' . '/includes/pages/case.php';
                break;
            case  'cases_list':
                require MODULES . 'module_page_open_case' . '/includes/pages/cases_list.php';
                break;
            case  'wins_list':
                require MODULES . 'module_page_open_case' . '/includes/pages/wins_list.php';
                break;
        } ?>
    </div>
<?php else : ?>
    <script type="text/javascript">
        const CASE_SPEED = <?= $settings['speed'] ?>;

        function localTimeMain(time) {
            var timestampInSeconds = time;
            var timestampInMilliseconds = timestampInSeconds * 1000;
            var date = new Date(timestampInMilliseconds);
            var day = String(date.getDate()).padStart(2, '0');
            var month = String(date.getMonth() + 1).padStart(2, '0');
            var year = date.getFullYear();
            var hours = String(date.getHours()).padStart(2, '0');
            var minutes = String(date.getMinutes()).padStart(2, '0');
            return `${day}.${month}.${year}.${hours}.${minutes}`;
        }
    </script>
    <div class="row">
        <div class="col-md-12">
            <div class="cases-main-block">
                <div class="donatggwp">
                    <div class="live-list-wrap">
                        <ul class="live-list" id="live_content"></ul>
                    </div>
                </div>
                <div class="live-list-wrap">
                    <ul class="live-list" id="live_content"></ul>
                </div>
                <?php if (isset($_SESSION['steamid32'])) : ?>
                    <div class="userbar__cases">
                        <div class="flexmenu_cases">
                            <div class="case_balance_name">
                                <?php if ($General->arr_general['avatars'] != 0) : $General->get_js_relevance_avatar($_SESSION['steamid64'], 1) ?>
                                    <img id="<?= empty($_SESSION['steamid']) ? 0 : $_SESSION['steamid'] ?>" ondrag="return false" ondragstart="return false" src="<?= empty($_SESSION['steamid']) ? $General->arr_general['site'] .  'storage/cache/img/avatars_random/' . rand(1, 30) . '_xs.jpg' : $General->getAvatar($_SESSION['steamid64'], 1) ?>" alt="profile">
                                <?php endif; ?>
                                <div class="secondblock__cases">
                                    <a class="username__cases" href="<?= empty($_SESSION['steamid32']) ? $General->arr_general['steam_only_authorization'] == 1 ? '?auth=login' : '#login' : $General->arr_general['site'] . 'profiles/' . $_SESSION['steamid'] . '/0/?search=1/' ?>">
                                        <?= action_text_clear(action_text_trim((empty($General->checkName($_SESSION['steamid64']))) ? $Auth->user_auth[0]['name'] : $General->checkName($_SESSION['steamid64']), 17)) ?>
                                    </a>
                                    <div class="userbalance_cases">
                                        <svg><use href="/resources/img/sprite.svg#wallet"></use></svg>
                                        <span id="balance_content"><?= number_format($CASES->Balance(), 0, ' ', ' ') ?> <?= $CASES->CaseSettings()['course'] ?></span>
                                    </div>
                                </div>
                                <button class="pay-link" data-openmodal="popupPay"><svg><use href="/resources/img/sprite.svg#plus"></use></svg> <?= $Translate->get_translate_phrase('_Purse_balance') ?></button>
                            </div>
                            <div class="case_userbar_btn">
                                <?php if (get_section('case', '')) : $subjects = $CASES->getCaseSubjects($_GET['case']) ?>
                                    <a class="button" href="<?= $General->arr_general['site'] ?>cases">
                                        <svg><use href="/resources/img/sprite.svg#box-closed"></use></svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_open_case', '_back') ?>
                                    </a>
                                <?php elseif (isset($_GET['wins']) && isset($_SESSION['steamid32'])) : $wins = $CASES->getWins(); ?>
                                    <a class="button" href="<?= $General->arr_general['site'] ?>cases">
                                        <svg><use href="/resources/img/sprite.svg#box-closed"></use></svg>
                                        <?= $Translate->get_translate_module_phrase('module_page_open_case', '_back') ?>
                                    </a>
                                <?php endif ?>
                                <a class="button" href="<?= $General->arr_general['site'] ?>cases/?wins">
                                    <svg><use href="/resources/img/sprite.svg#bag"></use></svg>
                                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_inventory') ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <br>
                <?php endif; ?>
                <?php if (get_section('case', '')) : $subjects = $CASES->getCaseSubjects($_GET['case']);
                    $price = $CASES->getPriceCase($_GET['case']); ?>
                    <?php if (empty($subjects)) : ?>
                        <script type="text/javascript">
                            window.location.replace("cases");
                        </script>
                    <?php else : ?>
                        <h2>
                            <svg viewBox="0 0 640 512">
                                <path d="M45.9 42.1c3-6.1 9.6-9.6 16.3-8.7L307 64 551.8 33.4c6.7-.8 13.3 2.7 16.3 8.7l41.7 83.4c9 17.9-.6 39.6-19.8 45.1L426.6 217.3c-13.9 4-28.8-1.9-36.2-14.3L307 64 223.6 203c-7.4 12.4-22.3 18.3-36.2 14.3L24.1 170.6C4.8 165.1-4.7 143.4 4.2 125.5L45.9 42.1zM308.1 128l54.9 91.4c14.9 24.8 44.6 36.6 72.5 28.6L563 211.6v167c0 22-15 41.2-36.4 46.6l-204.1 51c-10.2 2.6-20.9 2.6-31 0l-204.1-51C66 419.7 51 400.5 51 378.5v-167L178.6 248c27.8 8 57.6-3.8 72.5-28.6L305.9 128h2.2z" />
                            </svg>
                            <?= $price['case_name'] ?>
                        </h2>
                        <?php if (empty($_SESSION['steamid32'])) : ?>
                            <div class="auth_info" onclick="location.href='?auth=login'">
                                <svg><use href="/resources/img/sprite.svg#steam"></use></svg>
                                <div class="auth_info_text">
                                    <?= $Translate->get_translate_module_phrase('module_page_open_case', '_AuthInfo') ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <div class="roulette">
                                <div id="sound-point" class="sound-on" onclick="roulette_sound();" data-tippy-content="Настройка звуков" data-tippy-placement="left"></div>
                                <div class="roulette-slider">
                                    <div class="r-side"></div>
                                    <div class="r-side2"></div>
                                    <div class="top-arr">
                                        <svg width="74" height="65" viewBox="0 0 74 65" fill="none">
                                            <path d="M27.5676 5.72069C31.84 -1.40004 42.16 -1.40003 46.4324 5.7207L72.0043 48.3406C76.4034 55.6723 71.1221 65 62.5719 65H11.4281C2.87785 65 -2.40339 55.6723 1.99567 48.3405L27.5676 5.72069Z">
                                        </svg>
                                    </div>
                                    <div class="bottom-arr">
                                        <svg width="74" height="65" viewBox="0 0 74 65" fill="none">
                                            <path d="M27.5676 5.72069C31.84 -1.40004 42.16 -1.40003 46.4324 5.7207L72.0043 48.3406C76.4034 55.6723 71.1221 65 62.5719 65H11.4281C2.87785 65 -2.40339 55.6723 1.99567 48.3405L27.5676 5.72069Z">
                                        </svg>
                                    </div>
                                    <div class="roulette-area">
                                        <div id="roulette">
                                            <div class="roulette-inner" style="transform: translateX(-190px);"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php if ($price['case_type'] == 2) : $free = $CASES->getTimeFreeOpen($_SESSION['steamid32'], $price['id']);
                                $openDate = $price['case_price'] + $free['date'];
                                if ($openDate > time()) : ?>
                                    <script type="text/javascript" defer>
                                        document.addEventListener('DOMContentLoaded', function() {

                                            $(".Timer").eTimer({
                                                etType: 0,
                                                etDate: localTimeMain(<?= $openDate ?>),
                                                etTitleText: "",
                                                etTitleSize: 10,
                                                etShowSign: "<?= !empty($_GET['language']) ? $_GET['language'] : $General->arr_general['language']; ?>",
                                                etSep: ":",
                                                etTextColor: "var(--text-custom)",
                                                etFontFamily: "Arial Black",
                                                etNumberFontFamily: "Arial Black",
                                                etLastUnit: 4,
                                                etNumberSize: 18,
                                                etNumberColor: "white",
                                            });
                                        });
                                    </script>
                                    <h3><?= $Translate->get_translate_module_phrase('module_page_open_case', '_THROUGH') ?></h3>
                                    <div class="Timer"></div>
                                <?php else : ?>
                                    <div id="open-sum"> <?php if ($price['case_type'] == 1) : echo $price['case_price'] . ' ' . $CASES->CaseSettings()['course'];
                                                        else : echo "FREE";
                                                        endif ?></div>
                                    <div class="cases__open-buttons">
                                        <button id="open-case" class="open-case" onclick="open_case(<?= $_GET['case'] ?>)"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Open') ?></button>
                                        <button id="open-case-fast" class="open-case active" onclick="open_case_fast(<?= $_GET['case'] ?>)"><svg><use href="/resources/img/sprite.svg#bolt"></use></svg><?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenFast') ?></button>
                                    </div>
                                <?php endif ?>
                            <?php else : ?>
                                <div class="price_block_kostyl">
                                    <b class="price_text">Стоимость открытия:</b>
                                    <id class="price_case"><?php if ($price['case_type'] == 1) : echo $price['case_price'] . ' ' . $CASES->CaseSettings()['course'];
                                                            else : echo "FREE";
                                                            endif ?>
                                </div>
                                <button id="open-case" class="open-case knopkacases" onclick="open_case(<?= $_GET['case'] ?>)"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Open') ?></button>
                                <button id="open-case-fast" class="open-case knopkacases" onclick="open_case_fast(<?= $_GET['case'] ?>)"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_OpenFast') ?></button><br>
                                <div class="recommended">Рекомендуется открывать "Быстро"!</div>
                            <?php endif ?>
                        <?php endif; ?>
                        <div id="case-subjects">
                            <h2><?= $Translate->get_translate_module_phrase('module_page_open_case', '_ContentCase') ?></h2>
                            <div id="subjects">
                                <?php foreach ($subjects as $key) : ?>
                                    <div class="subject-block <?= $key['subject_class'] ?>">
                                        <div class="b-top"></div>
                                        <div class="b-bottom"></div>
                                        <div class="b-left"></div>
                                        <div class="b-right"></div>
                                        <div class="subject-services">
                                            <div class="subject-fix">
                                                <div class="subject-image-wrapper">
                                                    <img width="100" class="subject-image" src="<?= $General->arr_general['site'] . $key['subject_img'] ?>" alt="<?= $key['subject_name'] ?> <?= $key['subject_desc'] ?>">
                                                </div>
                                                <div class="subject">
                                                    <span><?= $key['subject_name'] ?></span>
                                                    <span><?= $key['subject_desc'] ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach ?>
                            </div>
                            <script type="text/javascript" src="<?= $General->arr_general['site'] ?>app/modules/module_page_open_case/assets/js/sweetalert2.all.js"></script>
                            <script>
                                document.addEventListener('DOMContentLoaded', function() {
                                    let script = document.createElement('script');
                                    script.src = "<?= $General->arr_general['site'] ?>app/modules/module_page_open_case/assets/js/roulette.js?<?= time() ?>";
                                    document.body.append(script);
                                    setTimeout(function() {
                                        load_roulette(<?= $_GET['case'] ?>)
                                    }, 200);
                                });
                            </script>
                        </div>
                    <?php endif; ?>
                <?php elseif (isset($_GET['wins']) && isset($_SESSION['steamid32'])) : $wins = $CASES->getWins(); ?>
                    <h2><?= $Translate->get_translate_module_phrase('module_page_open_case', '_MyWins') ?></h2>
                    <div id="wins">
                        <?php foreach ($wins as $key) : ?>
                            <div class="subject-block <?= $key['subject_style'] ?>">
                                <?php if ($key['sale']) : ?><div class="subject-price"><?= $key['sale'] . ' ' . $CASES->CaseSettings()['course'] ?></div><?php endif; ?>
                                <div class="b-top"></div>
                                <div class="b-bottom"></div>
                                <div class="b-left"></div>
                                <div class="b-right"></div>
                                <div class="subject-services" <?php if (!empty($key['up']) && empty($key['sell'])) : ?>style='opacity: 0.1;' <?php endif ?><?php if (empty($key['up']) && !empty($key['sell'])) : ?>style='opacity: 0.3;' <?php endif ?>>
                                    <div class="subject-fix">
                                        <div class="subject-image-wrapper">
                                            <img width="100" class="subject-image" src="<?= $General->arr_general['site'] . $key['subject_img'] ?>" alt="<?= $key['subject_name'] ?> <?= $key['subject_desc'] ?>">
                                        </div>
                                        <div class="subject" style="position: absolute;bottom: 2px;text-align: left;">
                                            <span><?= $key['subject_name'] ?></span>
                                            <span><?= $key['subject_desc'] ?></span>
                                        </div>
                                    </div>
                                </div>
                                <?php if (empty($key['up']) && empty($key['sell'])) : ?>
                                    <div class="subject-hover" id="rem<?= $key['id'] ?>">
                                        <a onclick="pick_up_wins(<?= $key['id'] ?>)">
                                            <?= $Translate->get_translate_module_phrase('module_page_open_case', '_Activate') ?>
                                        </a>
                                        <?php if ($key['sale'] != 0): ?>
                                            <a onclick="to_sale_wins(<?= $key['id'] ?>)">
                                                <?= $Translate->get_translate_module_phrase('module_page_open_case', '_Sell') ?> <?= $key['sale'] . ' ' . $CASES->CaseSettings()['course'] ?>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                                <?php if (empty($key['up']) && !empty($key['sell'])) : ?>
                                    <div class="subject-hover">
                                        <a>ПРОДАНО</a>
                                    </div>
                                <?php endif; ?>
                                <?php if (!empty($key['up']) && empty($key['sell'])) : ?>
                                    <div class="subject-hover">
                                        <a onclick="my_wins(<?= $key['id'] ?>)">
                                            СМОТРЕТЬ ВЫИГРЫШ
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach ?>
                    </div>
                    <script type="text/javascript" src="<?= $General->arr_general['site'] ?>app/modules/module_page_open_case/assets/js/sweetalert2.all.js"></script>
                <?php else : $cases = $CASES->getCases(); ?>
                    <div id="cases">
                        <?php foreach ($cats as $cat): ?>
                            <div class="cases-section__title">
                                <div class="new-title">
                                    <div class="new-title__text"><?= $cat['name'] ?></div>
                                </div>
                            </div>
                            <?php foreach ($cases as $key) : $opens = $CASES->getOpens($key['id']); ?>
                                <?php if ($key['case_cat'] === $cat['id']): ?>
                                    <div class="case-block">
                                        <a class="case-link" href="<?= set_url_section(get_url(2), 'case', $key['id']) ?>">
                                            <span class="case-img-wrap">
                                                <img class="case-img" src="<?= $General->arr_general['site'] ?><?= $key['case_img'] ?>">
                                            </span>
                                            <span class="case-span-wrap">
                                                <?php if ($key['case_type'] == 2) : ?>
                                                    <?php if (isset($_SESSION['steamid32'])) :
                                                        $free = $CASES->getTimeFreeOpen($_SESSION['steamid32'], $key['id']);
                                                        $openDate = $key['case_price'] + $free['date']; ?>
                                                        <?php if ($openDate > time()) : ?>
                                                            <script type="text/javascript" defer>
                                                                document.addEventListener('DOMContentLoaded', function() {
                                                                    $(".eTimer<?= $key['id'] ?>").eTimer({
                                                                        etType: 0,
                                                                        etDate: localTimeMain(<?= $openDate ?>),
                                                                        etTitleText: "",
                                                                        etTitleSize: 10,
                                                                        etShowSign: "<?= !empty($_GET['language']) ? $_GET['language'] : $General->arr_general['language']; ?>",
                                                                        etSep: ":",
                                                                        etTextColor: "var(--text-custom)",
                                                                        etFontFamily: "Arial Black",
                                                                        etNumberFontFamily: "Arial Black",
                                                                        etLastUnit: 4,
                                                                        etNumberSize: 18,
                                                                        etNumberColor: "white",
                                                                    });
                                                                });
                                                            </script>
                                                            <span class="case-name"><?= $key['case_name'] ?></span>
                                                            <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                            <div class="eTimer<?= $key['id'] ?>" style="top: -11px;position: relative;"></div>
                                                        <?php else : ?>
                                                            <span class="case-name"><?= $key['case_name'] ?></span>
                                                            <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                            <span class="case-price">FREE</span>
                                                        <?php endif ?>
                                                    <?php else : ?>
                                                        <span class="case-name"><?= $key['case_name'] ?></span>
                                                        <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                        <span class="case-price">FREE</span>
                                                    <?php endif ?>
                                                <?php else : ?>
                                                    <span class="case-name"><?= $key['case_name'] ?></span>
                                                    <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                    <span class="case-price"><?= $key['case_price'] . ' ' . $CASES->CaseSettings()['course'] ?></span>
                                                <?php endif ?>
                                            </span>
                                            <span></span>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach ?>
                        <?php endforeach ?>
                        <?php $case_num = 0;
                        foreach ($cases as $key) : $opens = $CASES->getOpens($key['id']);
                            ($key['case_cat'] == 0) ? $case_num++ : '' ?>
                            <?php if (($case_num == 1 && $key['case_cat'] == 0) && !empty($cats)): ?>
                                <div class="cases-section__title">
                                    <div class="new-title">
                                        <div class="new-title__text"><?= $Translate->get_translate_module_phrase('module_page_open_case', '_Uncategorized') ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <?php if ($key['case_cat'] == 0): ?>
                                <div class="case-block">
                                    <a class="case-link" href="<?= set_url_section(get_url(2), 'case', $key['id']) ?>">
                                        <span class="case-img-wrap">
                                            <img class="case-img" src="<?= $General->arr_general['site'] ?><?= $key['case_img'] ?>">
                                        </span>
                                        <span class="case-span-wrap">
                                            <?php if ($key['case_type'] == 2) : ?>
                                                <?php if (isset($_SESSION['steamid32'])) :
                                                    $free = $CASES->getTimeFreeOpen($_SESSION['steamid32'], $key['id']);
                                                    $openDate = $key['case_price'] + $free['date']; ?>
                                                    <?php if ($openDate > time()) : ?>
                                                        <script type="text/javascript" defer>
                                                            document.addEventListener('DOMContentLoaded', function() {
                                                                $(".eTimer<?= $key['id'] ?>").eTimer({
                                                                    etType: 0,
                                                                    etDate: localTimeMain(<?= $openDate ?>),
                                                                    etTitleText: "",
                                                                    etTitleSize: 10,
                                                                    etShowSign: "<?= !empty($_GET['language']) ? $_GET['language'] : $General->arr_general['language']; ?>",
                                                                    etSep: ":",
                                                                    etTextColor: "var(--text-custom)",
                                                                    etFontFamily: "Arial Black",
                                                                    etNumberFontFamily: "Arial Black",
                                                                    etLastUnit: 4,
                                                                    etNumberSize: 18,
                                                                    etNumberColor: "white",
                                                                });
                                                            });
                                                        </script>
                                                        <span class="case-name"><?= $key['case_name'] ?></span>
                                                        <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                        <div class="eTimer<?= $key['id'] ?>" style="top: -11px;position: relative;"></div>
                                                    <?php else : ?>
                                                        <span class="case-name"><?= $key['case_name'] ?></span>
                                                        <span class="case-open">Открыто:<?= $opens ?> раз(-а)</span>
                                                        <span class="case-price">FREE</span>
                                                    <?php endif ?>
                                                <?php else : ?>
                                                    <span class="case-name"><?= $key['case_name'] ?></span>
                                                    <span class="case-open">Открыто:<?= $opens ?> раз(-а)</span>
                                                    <span class="case-price">FREE</span>
                                                <?php endif ?>
                                            <?php else : ?>
                                                <span class="case-name"><?= $key['case_name'] ?></span>
                                                <span class="case-open">Открыто: <?= $opens ?> раз(-а)</span>
                                                <span class="case-price"><?= $key['case_price'] . ' ' . $CASES->CaseSettings()['course'] ?></span>
                                            <?php endif ?>
                                        </span>
                                        <span></span>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endforeach ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<style type="text/css">
    .align-center {
        margin: 25px auto;
        padding: 25px;
        border: 2px solid var(--span-color);
        box-shadow: var(--span-color-back) 5px 5px;
        border-radius: 2px;
    }
</style>