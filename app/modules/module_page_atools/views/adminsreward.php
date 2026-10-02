<div class="admtools__card">
    <div class="admtools__admreward">
        <h3 class="admtools__h3 mbe-0">Личный результат</h3>
        <div class="admtools__selfreward">
            <div class="admtools__selfreward-info flex-1">
                <div class="admtools__selfreward-title">с <time>07.10.24</time> по <time>13.10.24</time> наиграно</div>
                <div class="admtools__selfreward-value"><svg>
                        <use href="/app/modules/module_page_atools/assets/img/sprite.svg#timer"></use>
                    </svg> 1д. 20ч. 13м. 20с.</div>
            </div>
            <div class="admtools__selfreward-info flex-1">
                <div class="admtools__selfreward-title">Ваша награда</div>
                <div class="admtools__selfreward-value"><svg>
                        <use href="/app/modules/module_page_atools/assets/img/sprite.svg#reward"></use>
                    </svg> PREMIUM - 7 дней</div>
            </div>
            <div class="admtools__selfreward-info flex-1">
                <div class="admtools__selfreward-title">Прогресс выполнения нормы</div>
                <div class="admtools__selfreward-value"> <span class="admtools__selfreward-percent"><span class="admtools__selfreward-percent-line" style="width:90%"></span></span>90%</div>
            </div>
            <div class="admtools__btn admtools__btn-accent flex-1 h-auto admtools__btn-disabled">Забрать награду</div>
        </div>
        <h3 class="admtools__h3 mbe-0 flex radio__mobile">
            Онлайн админов
            <fieldset class="radios-chips ml-auto">
                <span>Показать: </span>
                <label class="radio">
                    <input class="radio__control visually-hidden" name="pages" type="radio" checked />
                    <span class="radio__label">10</span>
                </label>
                <label class="radio">
                    <input class="radio__control visually-hidden" name="pages" type="radio" />
                    <span class="radio__label">20</span>
                </label>
                <label class="radio">
                    <input class="radio__control visually-hidden" name="pages" type="radio" />
                    <span class="radio__label">50</span>
                </label>
            </fieldset>
        </h3>
        <div class="admtools__admreward-filters admtools__input-form">
            <div class="admtools__date flex-1">
                <input class="admtools__input admtools__input-datepicker w100" type="date" name="start-date" id="start-date" required>
                <label for="start-date" class="label-date">Начало</label>
            </div>
            <div class="admtools__date flex-1">
                <input class="admtools__input admtools__input-datepicker w100" type="date" name="end-date" id="end-date" required>
                <label for="end-date" class="label-date">Конец</label>
            </div>
            <div class="admtools__fake-select flex-1" open-select="">
                <span class="hide-long-text">Все сервера</span><svg class="admtools__fake-select-arrow">
                    <use href="/app/modules/module_page_atools/assets/img/sprite.svg#chevrondown"></use>
                </svg>
                <ul class="admtools__fake-select-list no-scrollbar scroll-200" id="">
                    <li data-server-id-add="null"><span class="hide-long-text">Все сервера</span>
                        <div class="admtools__radio-area choosen"><svg class="admtools__fake-select-radio">
                                <use href="/app/modules/module_page_atools/assets/img/sprite.svg#circle"></use>
                            </svg></div>
                    </li>
                    <li data-server-id-add=""><span class="hide-long-text">12</span>
                        <div class="admtools__radio-area"><svg class="admtools__fake-select-radio">
                                <use href="/app/modules/module_page_atools/assets/img/sprite.svg#circle"></use>
                            </svg></div>
                    </li>
                </ul>
            </div>
            <div class="admtools__fake-select flex-1" open-select="type-filter">
                <span class="hide-long-text">По норме</span><svg class="admtools__fake-select-arrow">
                    <use href="/app/modules/module_page_atools/assets/img/sprite.svg#chevrondown"></use>
                </svg>
                <ul class="admtools__fake-select-list no-scrollbar scroll-200" id="type-filter">
                    <li data-type-filter="1"><span class="hide-long-text">Все админы</span>
                        <div class="admtools__radio-area choosen"><svg class="admtools__fake-select-radio">
                                <use href="/app/modules/module_page_atools/assets/img/sprite.svg#circle"></use>
                            </svg></div>
                    </li>
                    <li data-type-filter="2"><span class="hide-long-text">Выполнили норму</span>
                        <div class="admtools__radio-area"><svg class="admtools__fake-select-radio">
                                <use href="/app/modules/module_page_atools/assets/img/sprite.svg#circle"></use>
                            </svg></div>
                    </li>
                    <li data-type-filter="3"><span class="hide-long-text">Не выполнили норму</span>
                        <div class="admtools__radio-area"><svg class="admtools__fake-select-radio">
                                <use href="/app/modules/module_page_atools/assets/img/sprite.svg#circle"></use>
                            </svg></div>
                    </li>
                </ul>
            </div>
            <input class="admtools__input flex-1" type="text" placeholder="STEAM_1:1:390... / 7656119803... / [U:1:1234234] / https://steamcommunity.com/profiles/..." value="" name="steamid" autocomplete="off">
            <div class="admtools__btn admtools__btn-red flex-1" id="resetFilters">Сбросить фильтр<svg>
                    <use href="/app/modules/module_page_atools/assets/img/sprite.svg#clear"></use>
                </svg></div>
        </div>
        <ul class="admtools__list">
            <li class="admtools__list-line">
                <div class="admtools__list-item">
                    <span class="hide-long-text admtools-table-avatar"><img class="hidden-from-table" width="30" height="30" src="https://avatars.steamstatic.com/fef49e7fa7e1997310d705b2a6158ff8dc1cdfeb_full.jpg" alt=""><span>Player</span></span>
                    <span class="hide-long-text hidden-from-table admtools-table-double"><span class="admtools__list-description">STEAMID</span>7656119114587</span>
                    <span class="hide-long-text hidden-from-table admtools-table-double"><span class="admtools__list-description">Наигранное время</span><time datetime="">2 д. 01 ч. 31 м. 43 с.</time></span>
                    <span class="admtools__list-plus"><svg>
                            <use href="/app/modules/module_page_atools/assets/img/sprite.svg#x"></use>
                        </svg></span>
                </div>
                <div class="admtools__list-action">
                    <div class="admtools__info-line flex-center-100">
                        <span>Информация</span>
                        <div class="admtools__list-buttons ml-auto">
                            <div data-tippy-content="<?= $Translate->get_translate_phrase('_Delete_Action') ?>" data-tippy-placement="top" class="admtools__list-button admtools__list-button-delete"><svg>
                                    <use href="/app/modules/module_page_atools/assets/img/sprite.svg#delete-trash"></use>
                                </svg></div>
                        </div>
                    </div>
                </div>
                <ul class="admtools__admreward-addon scroll scroll-200">
                    <li>
                        <span class="hidden-from-table">Сессия #1</span>
                        <span>Сервер ID: 1</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 1ч. 1м. 1с.</span>
                    </li>
                    <li>
                        <span class="hidden-from-table">Сессия #2</span>
                        <span>Сервер ID: 2</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 10ч. 1м. 1с.</span>
                    </li>
                    <li>
                        <span class="hidden-from-table">Сессия #3</span>
                        <span>Сервер ID: 3</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 1ч. 1м. 1с.</span>
                    </li>
                    <li>
                        <span class="hidden-from-table">Сессия #3</span>
                        <span>Сервер ID: 3</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 1ч. 1м. 1с.</span>
                    </li>
                    <li>
                        <span class="hidden-from-table">Сессия #3</span>
                        <span>Сервер ID: 3</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 1ч. 1м. 1с.</span>
                    </li>
                    <li>
                        <span class="hidden-from-table">Сессия #3</span>
                        <span>Сервер ID: 3</span>
                        <span class="hidden-from-table">Зашел: 01.01.2024</span>
                        <span class="hidden-from-table">Вышел: 01.01.2024</span>
                        <span>Наиграл: 1ч. 1м. 1с.</span>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</div>