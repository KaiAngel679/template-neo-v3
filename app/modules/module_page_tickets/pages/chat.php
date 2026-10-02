<div class="tickets__chat-wrapper">
    <div class="tickets__left-side">
        <div class="tickets__chat-header">
            <a href="/tickets/list/" class="tickets__chat-back">
                <svg>
                    <use href="/resources/img/sprite.svg#single-chevrone-left"></use>
                </svg>
                <?= $Translate->get_translate_module_phrase('module_page_tickets', '_back') ?>
            </a>
            <h2 class="tickets__h2"><?= action_text_clear($chatXuesos['topic']) ?></h2>
            <div class="tickets__chat-details">
                <span class="ticket__open-number"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_ticket') ?> №<?= $chatXuesos['id'] ?></span>•
                <div class="tickets__chat-state reviewed"><?php if ($chatXuesos['state'] == 1) {
                                                                echo $Translate->get_translate_module_phrase('module_page_tickets', '_waiting');
                                                            } elseif ($chatXuesos['state'] == 2) {
                                                                echo $Translate->get_translate_module_phrase('module_page_tickets', '_problemSolved');
                                                            } else {
                                                                echo $Translate->get_translate_module_phrase('module_page_tickets', '_autoClosed');
                                                            } ?></div>•
                <div class="tickets__chat-status"><?= $chatXuesos['status'] == 1 ? $Translate->get_translate_module_phrase('module_page_tickets', '_opened') : $Translate->get_translate_module_phrase('module_page_tickets', '_closed') ?></div>
            </div>
            <?php if ($chatXuesos['steamid_close']) : ?>
                <div class="tickets__chat-closedby"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_closedBy') ?> <a href="/profiles/<?= $chatXuesos['steamid_close'] ?>/?search=1"><?= $General->checkName($chatXuesos['steamid_close']) ?></a> • <?= date('d.m.Y, H:i', $chatXuesos['edit_at']) ?></div>
            <?php endif; ?>
            <?php if ($chatXuesos['server']) : ?>
                <span class="tickets__chat-server">
                    <svg>
                        <use href="/resources/img/sprite.svg#servers"></use>
                    </svg>
                    <?= $Translate->get_translate_phrase('_Server') ?>: <?= action_text_clear($ds->getServerName($chatXuesos['server'])) ?></span>
            <?php endif; ?>
        </div>
        <div class="tickets__chat-messages" id="chat-render"></div>
        <?php if ($chatXuesos['status'] == 1) : ?>
            <?php if ($access['access']) : ?>
                <div class="tickets__ready-answer">
                    <span><?= $Translate->get_translate_module_phrase('module_page_tickets', '_readyAnswers') ?></span>
                    <div class="tickets__ready-buttons">
                        <?php foreach ($jr->getCache('answers') as $key) : ?>
                            <button class="answer" data-text="<?= action_text_clear($key['text_answer']) ?>"><?= action_text_clear($key['text_button']) ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            <div class="ticket__message">
                <label for="message" class="ticket__message-label"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_sendMessage') ?></label>
                <textarea class="ticket__message-teaxtarea" id="message" placeholder="<?= $Translate->get_translate_module_phrase('module_page_tickets', '_textareaPlaceholderChat') ?>" autofocus required maxlength="1000" spellcheck="true" wrap="hard"></textarea>
                <input type="file" class="filepond-multiple">
                <input type="hidden" id="filepond">
                <?php if ($access['access']) : ?>
                    <div class="inputs-inline">
                        <input type="checkbox" class="switch" id="anonim">
                        <label for="anonim"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_anon') ?></label>
                    </div>
                <?php endif; ?>
                <div class="tickets__buttons">
                    <button class="active" id="send-message"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_sendMessageButton') ?><?php if (!$access['access']) : ?><svg id="slow-svg" style="display: none;">
                            <use href="/resources/img/sprite.svg#timer"></use>
                        </svg><span id="slow-time" class="tickets__timer" style="display: none;"></span><?php endif; ?></button>
                    <?php if ($access['access'] && $jr->getCache('settings')['slow']) : ?>
                        <button class="tickets__button-timer icon_btn_transparent <?= $cr->getSlowMode($id) == 1 ? 'activated' : '' ?>" id="slow-mode"><svg>
                                <use href="/resources/img/sprite.svg#timer"></use>
                            </svg></button>
                    <?php endif; ?>
                    <button class="margin-left-auto" id="close-ticket"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_closeTicket') ?></button>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php if ($access['access']): ?>
        <div class="tickets__right-side">
            <h2><?= $Translate->get_translate_module_phrase('module_page_tickets', '_aboutUser') ?></h2>
            <div class="tickets__right-blocks">
                <h3><?= $Translate->get_translate_module_phrase('module_page_tickets', '_currentPunish') ?></h3>
                <span class="tickets__right-subcat"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_bans') ?></span>
                <?php if ($cc->getPunish('ban', 'active')) : ?>
                    <ul class="tickets__right-list">
                        <?php foreach ($cc->getPunish('ban', 'active') as $key) : $admin = $ds->getAdminById($key['admin_id']); ?>
                            <li>
                                <span data-tippy-content="<?= action_text_clear($key['reason']) ?>" data-tippy-placement="top"><?= action_text_clear($key['reason']) ?></span>
                                <span class="flex-text"><svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg> <?= $key['duration'] == 0 ? $Translate->get_translate_phrase('_Forever') : $Modules->action_time_exchange_exact($key['duration']) ?></span>
                                <?= $admin['steamid'] ? '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg><a href="/profiles/' . $admin['steamid'] . '/?search=1">' . (empty($admin['name']) ? $General->checkName($admin['steamid']) : action_text_clear($admin['name'])) . '</a></span>' : '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg>Console</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noPunish') ?></div>
                <?php endif; ?>
                <span class="tickets__right-subcat"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_comms') ?></span>
                <?php if ($cc->getPunish('mute', 'active')) : ?>
                    <ul class="tickets__right-list tickets__right-list-comms">
                        <?php foreach ($cc->getPunish('mute', 'active') as $key) : $admin = $ds->getAdminById($key['admin_id']); ?>
                            <li>
                                <span><?php if ($key['unified_type'] == 0) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_mic');
                                        } elseif ($key['unified_type'] == 1) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_chat');
                                        } elseif ($key['unified_type'] == 2) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_chatMic');
                                        } ?></span>
                                <span data-tippy-content="<?= action_text_clear($key['reason']) ?>" data-tippy-placement="top"><?= action_text_clear($key['reason']) ?></span>
                                <span class="flex-text"><svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg> <?= $key['duration'] == 0 ? $Translate->get_translate_phrase('_Forever') : $Modules->action_time_exchange_exact($key['duration']) ?></span>
                                <?= $admin['steamid'] ? '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg><a href="/profiles/' . $admin['steamid'] . '/?search=1">' . (empty($admin['name']) ? $General->checkName($admin['steamid']) : action_text_clear($admin['name'])) . '</a></span>' : '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg>Console</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noPunish') ?></div>
                <?php endif; ?>
                <h3><?= $Translate->get_translate_module_phrase('module_page_tickets', '_historyPunish') ?></h3>
                <span class="tickets__right-subcat"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_bans') ?></span>
                <?php if ($cc->getPunish('ban', 'expired')) : ?>
                    <ul class="tickets__right-list">
                        <?php foreach ($cc->getPunish('ban', 'expired') as $key) : $admin = $ds->getAdminById($key['admin_id']); ?>
                            <li>
                                <span data-tippy-content="<?= action_text_clear($key['reason']) ?>" data-tippy-placement="top"><?= action_text_clear($key['reason']) ?></span>
                                <span class="flex-text"><svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg> <?= $key['duration'] == 0 ? $Translate->get_translate_phrase('_Forever') : $Modules->action_time_exchange_exact($key['duration']) ?></span>
                                <?= $admin['steamid'] ? '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg><a href="/profiles/' . $admin['steamid'] . '/?search=1">' . (empty($admin['name']) ? $General->checkName($admin['steamid']) : action_text_clear($admin['name'])) . '</a></span>' : '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg>Console</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noPunish') ?></div>
                <?php endif; ?>
                <span class="tickets__right-subcat"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_comms') ?></span>
                <?php if ($cc->getPunish('mute', 'expired')) : ?>
                    <ul class="tickets__right-list tickets__right-list-comms">
                        <?php foreach ($cc->getPunish('mute', 'expired') as $key) : $admin = $ds->getAdminById($key['admin_id']); ?>
                            <li>
                                <span><?php if ($key['unified_type'] == 0) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_mic');
                                        } elseif ($key['unified_type'] == 1) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_chat');
                                        } elseif ($key['unified_type'] == 2) {
                                            echo $Translate->get_translate_module_phrase('module_page_tickets', '_chatMic');
                                        } ?></span>
                                <span data-tippy-content="<?= action_text_clear($key['reason']) ?>" data-tippy-placement="top"><?= action_text_clear($key['reason']) ?></span>
                                <span class="flex-text"><svg>
                                        <use href="/resources/img/sprite.svg#time-expired"></use>
                                    </svg> <?= $key['duration'] == 0 ? $Translate->get_translate_phrase('_Forever') : $Modules->action_time_exchange_exact($key['duration']) ?></span>
                                <?= $admin['steamid'] ? '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg><a href="/profiles/' . $admin['steamid'] . '/?search=1">' . (empty($admin['name']) ? $General->checkName($admin['steamid']) : action_text_clear($admin['name'])) . '</a></span>' : '<span><svg><use href="/resources/img/sprite.svg#shield-check"></use></svg>Console</span>' ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noPunish') ?></div>
                <?php endif; ?>
                <h3><?= $Translate->get_translate_module_phrase('module_page_tickets', '_historyPay') ?></h3>
                <?php if ($cc->getLkHistory()) : ?>
                    <ul class="tickets__right-list tickets__right-list-five">
                        <?php foreach ($cc->getLkHistory() as $key) : ?>
                            <li>
                                <span class="flex-text"><?= $key['pay_data'] ?></span>
                                <span><img class="payment-image" src="/app/modules/module_page_pay/assets/gateways/<?= strtolower($key['pay_system']) ?>.svg"></span>
                                <span class="flex-text"><?= action_text_clear($key['pay_summ']) ?></span>
                                <span><?= action_text_clear($key['pay_promo'] ?? '-') ?></span>
                                <?php if ($key['pay_status'] == 1) : ?>
                                    <span><svg class="green">
                                            <use href="/resources/img/sprite.svg#check"></use>
                                        </svg></span>
                                <?php else : ?>
                                    <span><svg class="red">
                                            <use href="/resources/img/sprite.svg#x"></use>
                                        </svg></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noPay') ?></div>
                <?php endif; ?>
                <h3><?= $Translate->get_translate_module_phrase('module_page_tickets', '_historyBuy') ?></h3>
                <?php if ($cc->getStoreHistory() && file_exists(MODULES . 'module_page_store/description.json')) : ?>
                    <ul class="tickets__right-list tickets__right-list-store">
                        <?php foreach ($cc->getStoreHistory() as $key) : ?>
                            <li>
                                <span data-tippy-content="<?= action_text_clear($key['date']) ?>" data-tippy-placement="top"><?= action_text_clear($key['date']) ?></span>
                                <span data-tippy-content="<?= action_text_clear($key['server']) ?>" data-tippy-placement="top"><?= action_text_clear($key['server']) ?></span>
                                <span data-tippy-content="<?= action_text_clear($key['title']) ?>" data-tippy-placement="top"><?= action_text_clear($key['title']) ?></span>
                                <span data-tippy-content="<?= action_text_clear($key['steam']) ?>" data-tippy-placement="top"><?= action_text_clear($key['steam']) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else : ?>
                    <div class="tickets__right-empty"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_noBuy') ?></div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>