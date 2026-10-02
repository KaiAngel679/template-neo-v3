<div class="popup_modal" id="addBan">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userAddBan') ?>
            <span class="popup_modal_close"><svg><use href="/resources/img/sprite.svg#x"></use></svg></span>
        </div>
        <hr>
        <form id="addBlockForm" enctype="multipart/form-data" method="post">
            <div class="contact_body">
                <div class="users__form">
                    <div class="inputs-inline">
                        <label for="userSteam64"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userSteamID') ?></label>
                        <input id="userSteam64" type="text" placeholder="76561198032362775" name="ban_steam">
                    </div>
                    <div class="inputs-inline">
                        <label for="userIp"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userIpAddress') ?></label>
                        <input id="userIp" type="text" placeholder="192.168.1.1" name="ban_ip">
                    </div>
                    <div class="inputs-inline">
                        <label for="userReason"><?= $Translate->get_translate_phrase('_Reason') ?></label>
                        <input id="userReason" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_adminpanel','_reasonExample') ?>" name="ban_reason">
                    </div>
                </div>
            </div>
        </form>
        <button class="width-100" type="submit" form="addBlockForm"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_issueBan') ?></button>
    </div>
</div>
<div class="popup_modal" id="addRole">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addRole') ?>
            <span class="popup_modal_close"><svg><use href="/resources/img/sprite.svg#x"></use></svg></span>
        </div>
        <hr>
        <form id="addRoleForm" enctype="multipart/form-data" method="post">
            <div class="contact_body">
                <div class="users__form">
                    <div class="inputs-inline">
                        <label for="addRoleName"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_roleName') ?></label>
                        <input type="text" name="add_role" placeholder="<?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_specifyName') ?>" id="addRoleName">
                    </div>
                    <div class="inputs-inline">
                        <label for=""><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_roleColor') ?></label>
                        <input type="" name="add_role_color" data-jscolor="">
                    </div>
                </div>
            </div>
        </form>
        <button class="width-100" type="submit" form="addRoleForm"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addRole') ?></button>
    </div>
</div>
<div class="popup_modal" id="giveRole">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_giveRole') ?>
            <span class="popup_modal_close"><svg><use href="/resources/img/sprite.svg#x"></use></svg></span>
        </div>
        <hr>
        <form id="addUserRoleForm" enctype="multipart/form-data" method="post">
            <div class="contact_body">
                <div class="users__form">
                    <div class="inputs-inline">
                        <label for="addRoleSteam"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userSteamID') ?></label>
                        <input type="text" placeholder="steamid64" name="steamid64" id="addRoleSteam">
                    </div>
                    <label for="rolesList"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userSteamID') ?></label>
                    <div class="inputs-inline">
                        <select name="role_id" placeholder="1" id="rolesList">
                            <?php foreach ($roles as $role): ?>
                                <option value="<?= $role['id'] ?>"><?= $role['role'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>
        </form>
        <button class="width-100" type="submit" form="addUserRoleForm"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_giveRole') ?></button>
    </div>
</div>
<div class="popup_modal" id="addAccess">
    <div class="popup_modal_content no-close no-scrollbar">
        <div class="popup_modal_head">
            <?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addSiteAdmin') ?>
            <span class="popup_modal_close"><svg><use href="/resources/img/sprite.svg#x"></use></svg></span>
        </div>
        <hr>
        <form id="addAdminForm" enctype="multipart/form-data" method="post">
            <div class="contact_body">
                <div class="users__form">
                    <div class="inputs-inline">
                        <label for="adminName"><?= $Translate->get_translate_phrase('_User') ?></label>
                        <input type="text" placeholder="" name="admin_name" id="adminName">
                    </div>
                    <div class="inputs-inline">
                        <label for="adminSteamId"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_userSteamID') ?></label>
                        <input type="text" placeholder="steamid64" name="admin_steamid64" id="adminSteamId">
                    </div>
                    <div class="inputs-inline">
                        <label for="adminAccess"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_adminAccess') ?></label>
                        <input type="text" placeholder="1-100" name="admin_access" id="adminAccess">
                    </div>
                    <div class="inputs-inline">
                        <label for="adminFlag"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_accessFlag') ?></label>
                        <input type="text" placeholder="a-z" name="admin_flags" id="adminFlag">
                    </div>
                </div>
            </div>
        </form>
        <button class="width-100" type="submit" form="addAdminForm"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_giveAccess') ?></button>
    </div>
</div>
<div class="col-md-12">
    <div class="users__header">
        <h3><svg><use href="/resources/img/sprite.svg#three-users"></use></svg><?= $Translate->get_translate_phrase('_Current_Action') ?></h3>
        <div class="users__header-buttons">
            <button data-openmodal="addBan"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_block') ?></button>
            <button data-openmodal="giveRole"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_giveRole') ?></button>
            <button data-openmodal="addRole"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_createRole') ?></button>
            <button data-openmodal="addAccess"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_addSiteAdmin') ?></button>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card height-100">
        <div class="card-header">
            <h4 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_customRoles') ?></h4>
        </div>
        <div class="card-container">
            <?php if (empty($roles)): ?>
                <div class="users-empty"><?= $Translate->get_translate_phrase('_emptySmile') ?></div>
            <?php else: ?>
                <div class="users__roles">
                    <?php foreach ($roles as $role): ?>
                        <div class="user__roles-wrapper" id="role-<?= $role['id'] ?>">
                            <div class="users__roles-role" style="color: <?= $role['color'] ?>; background-color: <?= str_replace(')', ', 10%)', $role['color']) ?>;">
                                <span style="background-color: <?= $role['color'] ?>;"></span>
                                <?= $role['role'] ?>
                                <svg><use href="/resources/img/sprite.svg#chevron-down"></use></svg>
                            </div>
                            <div class="user__roles-delete" id="del_role" id_del="<?= $role['id'] ?>">
                                <svg><use href="/resources/img/sprite.svg#x"></use></svg>
                            </div>
                        </div>
                        <ul class="users__roles-list scroll no-scrollbar">
                            <?php foreach ($role['users'] as $user): ?>
                                <?php $General->get_js_relevance_avatar($user) ?>
                                <li id="role-users-<?= $role['id'] ?>-<?= $user ?>">
                                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= $user ?>?search=1" id="name" nameid="<?= $user ?>">
                                        <?= $General->checkName($user) ?>
                                    </a>
                                    <svg id="del_user_role" user_del="<?= $user ?>" role_del="<?= $role['id'] ?>"><use href="/resources/img/sprite.svg#x"></use></svg>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="col-md-3">
    <div class="card height-100">
        <div class="card-header">
            <h4 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_siteAdmins') ?></h4>
        </div>
        <div class="card-container">
            <?php if (empty($admins)): ?>
                <div class="users-empty"><?= $Translate->get_translate_phrase('_emptySmile') ?></div>
            <?php else: ?>
                <div class="users__admins">
                    <?php foreach ($admins as $admin): ?>
                        <?php $General->get_js_relevance_avatar($admin['steamid']) ?>
                        <div class="users__admins-wrapper" id="admin-<?= $admin['steamid'] ?>">
                            <div class="users__admins-details">
                                <span>
                                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= $admin['steamid'] ?>?search=1" id="name" nameid="<?= $admin['steamid'] ?>">
                                        <?= $General->checkName($admin['steamid']) ?>
                                    </a>
                                </span>
                                <span><?= $Translate->get_translate_phrase('_Flag') ?>: <?= $admin['flags'] ?>, <?= $Translate->get_translate_phrase('_access') ?>: <?= $admin['access'] ?></span>
                            </div>
                            <div class="users__admins-delete users-delete" id="del_admin" id_del="<?= $admin['steamid'] ?>"><svg><use href="/resources/img/sprite.svg#x"></use></svg></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<div class="col-md-6">
    <div class="card height-100">
        <div class="card-header">
            <h4 class="badge"><?= $Translate->get_translate_module_phrase('module_page_adminpanel', '_BlockedUsers') ?></h4>
        </div>
        <div class="card-container">
            <?php if (empty($blocks)): ?>
                <div class="users-empty"><?= $Translate->get_translate_phrase('_emptySmile') ?></div>
            <?php else: ?>
                <ul class="users__banned">
                    <?php foreach ($blocks as $block): ?>
                        <li id="block-<?= $block['id'] ?>">
                            <div>
                                <span>
                                    <?php $General->get_js_relevance_avatar($block['steam']) ?>
                                    <a href="<?= $General->arr_general['site'] ?>profiles/<?= $block['steam'] ?>/?search=1" target="_blank" id="avatar" nameid="<?= $block['steam'] ?>">
                                        <?= $General->checkName($block['steam'])?>
                                    </a>
                                </span>
                                <span><?= $Translate->get_translate_phrase('_Reason') ?>: <b><?= empty($block['reason']) ? $Translate->get_translate_phrase('_absent') : $block['reason'] ?></b></span>
                            </div>
                            <div class="visability-hidden">
                                <span><?= $block['steam'] ?></span>
                                <span>IP: <?= empty($block['ip']) ? $Translate->get_translate_phrase('_absent') : $block['ip'] ?></span>
                            </div>
                            <div>
                                <button class="button-delete" id="del_block" ban-id="<?= $block['id'] ?>"><?= $Translate->get_translate_phrase('_Delete_Action') ?></button>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</div>
