<?php !defined("IN_LR") && die() ?>
<div class="back">
    <h2><?= $options['language'] == 'EN' ? 'Database setup' : 'Настройка базы данных' ?></h2>
    <hr>
    <form enctype="multipart/form-data" method="post">
        <div class="db_left">
            <div class="inputs-inline">
                <label for="host" class="input_text">Host</label>
                <input id="host" type="text" name="HOST" value="<?php !empty($_POST['HOST']) && print $_POST['HOST'] ?>" required>
            </div>
            <div class="inputs-inline">
                <label for="user" class="input_text">User</label>
                <input id="user" type="text" name="USER" value="<?php !empty($_POST['USER']) && print $_POST['USER'] ?>" required>
            </div>
            <div class="inputs-inline">
                <label for="db" class="input_text">Database</label>
                <input id="db" type="text" name="DATABASE" value="<?php !empty($_POST['DATABASE']) && print $_POST['DATABASE'] ?>" required>
            </div>
            <div class="inputs-inline">
                <label for="pass" class="input_text">Password</label>
                <div class="number">
                    <input id="pass" type="password" name="PASS" value="<?php !empty($_POST['PASS']) && print $_POST['PASS'] ?>" required>
                    <div class="eye-password" id="show_pass">
                        <svg>
                            <use href="/resources/img/sprite.svg#eye"></use>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="check"><button class='button width-100' name="db_check" type="submit" value=""><?= $options['language'] == 'EN' ? 'Check' : 'Проверить' ?></button></div>
        </div>
    </form>
</div>