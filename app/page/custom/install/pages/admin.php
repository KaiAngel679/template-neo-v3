<?php !defined("IN_LR") && die() ?>
<div class="back">
    <h2><?= $options['language'] == 'EN' ? 'Administrator' : 'Администратор' ?></h2>
    <hr>
    <?php if (empty($data['avatar'])) : ?>
        <form enctype="multipart/form-data" method="post">
            <div class="webkey_form">
                <div class="inputs-inline">
                    <label for="admin">STEAM ID</label>
                    <input id="admin" name="admin" value="" placeholder="STEAM_1:1:390... / 7656119803... / [U:1:1234234] / https://steamcommunity.com/profiles/...">
                </div>
                <button class="width-100" name="check_admin_steam" type="submit"><?= $options['language'] == 'EN' ? 'Check' : 'Проверить' ?></button>
            </div>
        </form>
    <?php else : ?>
        <form enctype="multipart/form-data" method="post">
            <div class="webkey_form">
                <div class="admin_zone">
                    <img src="<?= $data['avatarfull'] ?>">
                    <div class="admin_name"><?= $data['personaname'] ?></div>
                    <hr>
                    <div class="admin_que">
                        <div class="admin_text"><?= $options['language'] == 'EN' ? 'This is your account?' : 'Это ваш аккаунт?' ?></div>
                        <div class="admin_buttons">
                            <button class="width-100" name="check_admin_steam_da" type="submit"><?= $options['language'] == 'EN' ? 'Yes' : 'Да' ?></button>
                            <button class="button-delete width-100" name="check_admin_steam_net" type="submit"><?= $options['language'] == 'EN' ? 'No' : 'Нет' ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    <?php endif ?>
</div>