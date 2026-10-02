<?php !defined("IN_LR") && die() ?>
<div class="back">
    <h2>STEAM WEB API KEY</h2>
    <hr>
    <form enctype="multipart/form-data" method="post">
        <div class="webkey_form">
            <div class="inputs-inline">
                <label for="wepapi" class="input_text">Password</label>
                <div class="number">
                    <input type="password" id="wepapi" name="web_key" value="<?php !empty($error) && $error == true && print 'WEB API KEY - ERROR' ?>" placeholder="Введите STEAM WEB API KEY" required>
                    <div class="eye-password" id="show_pass">
                        <svg>
                            <use href="/resources/img/sprite.svg#eye"></use>
                        </svg>
                    </div>
                </div>
            </div>
            <button class="width-100" name="check" type="submit"><?= $options['language'] == 'EN' ? 'Check' : 'Проверить' ?></button>
        </div>
    </form>
</div>