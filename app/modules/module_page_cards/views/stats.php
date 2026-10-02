<div class="row">
    <div class="col-md-7">
        <div class="stats__counters-wrapper">
            <div class="stats__counter-opened">
                <?php $stats = $csc->getCountOpenCards() ?>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_totalOpened') ?></p>
                    <span><?= $stats['total_all_time'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_todayOpened') ?></p>
                    <span><?= $stats['today'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_yesterdayOpened') ?></p>
                    <span><?= $stats['yesterday'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_last7DaysOpened') ?></p>
                    <span><?= $stats['last_7_days'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_last30DaysOpened') ?></p>
                    <span><?= $stats['last_30_days'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  today">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_last90DaysOpened') ?></p>
                    <span><?= $stats['last_90_days'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
            </div>
            <div class="stats__counter-buy">
                <div class="stats__counter-block  money">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_moneyIssued') ?></p>
                    <span><?= $csc->getCountMoney()['money_total'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#wallet"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  money">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_paidOpening') ?></p>
                    <span><?= $stats['buy_open_total'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#game-cards"></use>
                    </svg>
                </div>
                <div class="stats__counter-block  shop">
                    <p><?= $Translate->get_translate_module_phrase('module_page_cards', '_issuedCredits') ?></p>
                    <span><?= $csc->getCountMoney()['credits_total'] ?></span>
                    <svg>
                        <use href="/resources/img/sprite.svg#coins"></use>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_cards', '_top100Players') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="stats__top-wrapper">
                    <div class="stats__top-header">
                        <span><?= $Translate->get_translate_module_phrase('module_page_cards', '_place') ?></span>
                        <span><?= $Translate->get_translate_module_phrase('module_page_cards', '_player') ?></span>
                        <span><?= $Translate->get_translate_module_phrase('module_page_cards', '_openedCards') ?></span>
                    </div>
                    <?php foreach($csc->getTopUsersOpens() as $index => $user): ?>
                        <div class="stats__top-item">
                            <span class="stats__top-rank">#<?= $index + 1 ?></span>
                            <a href="/profiles/<?= $user['steamid'] ?>/?search=1" class="stats__top-username">
                                <?= $General->get_js_relevance_avatar($user['steamid']); ?>
                                <img src="<?= $General->getAvatar($user['steamid'], 3); ?>" id="avatar" avatarid="<?= $user['steamid'] ?>">
                                <span><?= $General->checkName($user['steamid']) ?></span> 
                            </a>
                            <span class="stats__top-count"><?= $user['opens'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <h3><?= $Translate->get_translate_module_phrase('module_page_cards', '_currentStreaksUsers') ?></h3>
        <div class="steps">
            <?php foreach($csc->getAllUsersStreak() as $key): ?>
                <div class="step <?= ($key['streak'] == 5 || $key['streak'] == 6) ? 'step--rare' : ($key['streak'] == 7 ? 'step--extrarare' : '') ?>">
                    <span class="step__title"><?= $key['streak'] ?> <?= $Translate->get_translate_module_phrase('module_page_cards', '_streaktext') ?></span>
                    <span class="step__reward">
                        <svg>
                            <use href="/resources/img/sprite.svg#game-cards"></use>
                        </svg>
                        <?= $key['count'] ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>