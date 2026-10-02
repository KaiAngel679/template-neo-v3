<div class="col-md-9">
    <div class="card">
        <div class="card-header">
            <div class="badge">Расширенная статистика</div>
        </div>
        <div class="card-container">
            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_First_round_kills'); ?>
            <?= $Player->get_unusualkills_op() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Penetrated_kills'); ?>
            <?= $Player->get_unusualkills_penetrated() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Killing_without_scope'); ?>
            <?= $Player->get_unusualkills_noscope() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Kills_on_run'); ?>
            <?= $Player->get_unusualkills_run() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Kills_flash'); ?>
            <?= $Player->get_unusualkills_flash() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Jump_kills'); ?>
            <?= $Player->get_unusualkills_jump() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Smoke_kills'); ?>
            <?= $Player->get_unusualkills_smoke() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Kills_whirl'); ?>
            <?= $Player->get_unusualkills_whirl() ?>

            <?= $Translate->get_translate_module_phrase('module_page_profiles', '_Kills_last_shoot'); ?>
            <?= $Player->get_unusualkills_last_clip() ?>
        </div>
        Надо сделать, если выпустят модуль по картам и оружиям
    </div>
</div>