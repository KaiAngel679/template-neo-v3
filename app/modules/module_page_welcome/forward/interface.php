<?php
$site_url = '';
if (isset($General) && isset($General->arr_general['site']) && is_string($General->arr_general['site'])) {
    $site_url = rtrim($General->arr_general['site'], '/');
}
?>
<div class="popup_modal" id="WelcomeModal">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            Добро пожаловать!
            <span class="popup_modal_close">
                <svg>
                    <use href="/resources/img/sprite.svg#x"></use>
                </svg>
            </span>
        </div>
        <div class="welcome-content">
        <div class="welcome-icon">
                <img src="<?php echo $site_url ? $site_url . '/app/modules/module_page_welcome/assets/gift.png' : 'app/modules/module_page_welcome/assets/gift.png'; ?>" alt="gift">
        </div>
        <div>
            <p>Мы рады видеть вас на нашем сайте.</p>
            <p>Заходите играть!</p>
            <div class="button_welcome">
               <button id="disabledWelcomeModal">Не показывать 1 день.</button>
            </div>
        </div>
        </div>
    </div>
</div>