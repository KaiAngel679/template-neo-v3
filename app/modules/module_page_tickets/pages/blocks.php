<h2 class="tickets__h2"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_settingsAccess') ?></h2>
<div class="tickets__blocks">
    <div class="tickets__blocks-block">
        <div class="card">
            <div class="card-header">
                <div class="badge">
                    <?= $Translate->get_translate_module_phrase('module_page_tickets', '_addingBlock') ?>
                </div>
            </div>
            <div class="card-container">
                <div class="inputs-inline">
                    <label for="adminSteam">STEAMID</label>
                    <input type="text" id="adminSteam" placeholder="STEAMID 64" required>
                </div>
                <div class="inputs-inline">
                    <label for="reasonBlock"> <?= $Translate->get_translate_phrase('_Reason') ?></label>
                    <input type="text" id="reasonBlock" placeholder="" required>
                </div>
                <div class="inputs-inline">
                    <label for="timeBlock"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_durationBlock') ?></label>
                    <input type="number" id="timeBlock" placeholder="" required>
                </div>
                <hr>
                <button class="width-100" id="send-form"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_addBlock') ?></button>
            </div>
        </div>
    </div>
    <div class="tickets__blocks-block">
        <div class="card">
            <div class="card-header">
                <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_blockList') ?></div>
            </div>
            <div class="card-container">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="text-align: left"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_blocked') ?></th>
                                <th style="text-align: left"><?= $Translate->get_translate_phrase('_Reason') ?></th>
                                <th style="text-align: left"><?= $Translate->get_translate_module_phrase('module_page_tickets', '_duration') ?></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ds->getBlocks() as $key) : ?>
                                <tr>
                                    <td>
                                        <span class="ticket__accesses-nickname"><a href="/profiles/<?= $key['steamid'] ?>/?search=1" id="name" nameid="<?= $key['steamid'] ?>"><?= $General->checkName($key['steamid']) ?></a></span>
                                    </td>
                                    <td>
                                        <div>
                                            <span class="ticket__accesses-nickname ticket__block-reason"><a><?= action_text_clear($key['reason']) ?></a></span>
                                        </div>
                                    </td>
                                    <td>
                                        <div><?= $key['duration'] == 0 ? $Translate->get_translate_phrase('_Forever') : $Modules->action_time_exchange_exact($key['duration'] + $key['created_at'] - time()) ?></div>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button class="button-delete delete" data-steamid="<?= $key['steamid'] ?>"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
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
</div>