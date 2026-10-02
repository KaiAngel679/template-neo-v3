<section class="row">
    <div class="col-md-12">
        <?php if ($page != 'chat') : ?>
            <h1 class="tickets__h1"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_tickets') ?></h1>
            <nav class="tickets__nav">
                <div class="tickets__nav-arrow" id="ticketArrow">
                    <svg><use href="/resources/img/sprite.svg#single-chevrone-right"></use></svg>
                </div>
                <ul class="tickets__nav-list scroll">
                    <li>
                        <a href="/tickets/send/" class="tickets__nav-item <?= $page == 'send' ? 'ticket-active' : '' ?>">
                            <svg><use href="/resources/img/sprite.svg#homepage"></use></svg>
                            <?= $Translate->get_translate_module_phrase('module_page_tickets', '_ticketsHome') ?>
                        </a>
                    </li>
                    <?php if ($_SESSION['steamid64']) : ?>
                        <li>
                            <a href="/tickets/list/" class="tickets__nav-item <?= $page == 'list' ? 'ticket-active' : '' ?>">
                                <svg><use href="/resources/img/sprite.svg#list"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_tickets', '_openTickets') ?>
                            </a>
                        </li>
                        <li>
                            <a href="/tickets/archive/" class="tickets__nav-item <?= $page == 'archive' ? 'ticket-active' : '' ?>">
                                <svg><use href="/resources/img/sprite.svg#archive"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_tickets', '_archive') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($access['add_block']) : ?>
                        <li>
                            <a href="/tickets/blocks/" class="tickets__nav-item <?= $page == 'blocks' ? 'ticket-active' : '' ?>">
                                <svg><use href="/resources/img/sprite.svg#user-block"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_tickets', '_userBlock') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($access['add_settings']) : ?>
                        <li>
                            <a href="/tickets/settings/" class="tickets__nav-item <?= $page == 'settings' ? 'ticket-active' : '' ?>">
                                <svg><use href="/resources/img/sprite.svg#gear"></use></svg>
                                <?= $Translate->get_translate_phrase('_Settings') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <?php if ($access['add_access']) : ?>
                        <li>
                            <a href="/tickets/access/" class="tickets__nav-item <?= $page == 'access' ? 'ticket-active' : '' ?>">
                                <svg><use href="/resources/img/sprite.svg#key"></use></svg>
                                <?= $Translate->get_translate_module_phrase('module_page_tickets', '_access') ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        <?php endif; ?>
        
        <?php require MODULES . "module_page_tickets/forward/vendors.php"; require MODULES . "module_page_tickets/forward/scripts.php";require MODULES . "module_page_tickets/pages/$page.php"; ?>
    </div>
</section>