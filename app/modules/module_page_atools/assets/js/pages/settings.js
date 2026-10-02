$(document).ready(function () {
    if ($('#maxWarnCount').length) {
        const readMaxWarns = function () {
            const $input = $('#maxWarnCount');
            const parsed = parseInt(String($input.val()).trim(), 10);

            if (Number.isFinite(parsed) && parsed >= 1) {
                return parsed;
            }

            const fallback = parseInt(String($input.prop('defaultValue') || $input.attr('min') || '1').trim(), 10);

            return Number.isFinite(fallback) && fallback >= 1 ? fallback : 1;
        };

        const saveGeneralSettings = function () {
            sendRequest({
                save_settings: true,
                max_warns: readMaxWarns(),
                auto_delete_admin_max_warns: $('#removeAdminWarn').is(':checked') ? 1 : 0,
                debug_logs: $('#debugLogs').is(':checked') ? 1 : 0,
                hide_vip_test: $('#hideTest').is(':checked') ? 1 : 0,
                vip_test_group: String($('#testName').val() || '').trim(),
                blockdb_api_key: String($('#blockdbApiKey').val() || '').trim(),
                default_all_servers: $('#defaultAllServers').is(':checked') ? 1 : 0,
            })
            .done(function (result) {
                if (result.status !== 'success') {
                    noty(result.message, result.status);
                }
            });
        };

        $('#removeAdminWarn, #debugLogs, #hideTest, #testName, #defaultAllServers').on('change', saveGeneralSettings);
        $('#maxWarnCount, #blockdbApiKey').on('change', saveGeneralSettings);
    }

    $('#importMsSettings').on('click', function () {
        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmImportMs'),
            confirmText: get_translate_module_phrase('module_page_atools', '_at_import'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_cancel'),
            onConfirm: function () {
                sendRequest({ import_managersystem: true })
                    .done(function (result) {
                        noty(result.message, result.status);
                    });
            }
        });
    });

    $('#dismissWelcomeModal').on('click', function () {
        sendRequest({ dismiss_welcome_modal: true })
            .done(function () {
                $('#newModule').removeClass('visible');
            });
    });

    $('#addAtoolsGroup').on('click', function () {
        const name = {};
        var $btn = $(this);

        $('.at__settings-adding [id^="groupName"]').each(function () {
            const lang = this.id.replace('groupName', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        const permissions = [];
        $('input[name="group-flag-add"]:checked').each(function () {
            if ($(this).val()) {
                permissions.push($(this).val());
            }
        });

        sendRequestWithButton($btn, { create_access_group: true, name: name, permissions: permissions })
            .done(function (result) {
                if (result.status == 'success') {
                    location.reload();
                } else {
                    noty(result.message, result.status);
                }
            });
    });

    $(document).on('click', '.at-btn-group-edit', function () {
        const $row = $(this).closest('tr');
        const groupData = $row.attr('data-group');

        if (!groupData) {
            return;
        }

        let group;
        try {
            group = JSON.parse(groupData);
        } catch (error) {
            return;
        }

        $('#editGroupId').val($row.attr('data-group-id'));

        $('#editGroupAtools [id^="groupName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('groupName', '').replace('Edit', '').toLowerCase();
            $(this).val(group.name[lang] || '');
        });

        $('input[name="group-flag-edit"]').each(function () {
            const value = $(this).val();
            $(this).prop('checked', value ? group.permissions.includes(value) : false);
        });
    });

    $('#saveAtoolsGroupEdit').on('click', function () {
        const name = {};

        $('#editGroupAtools [id^="groupName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('groupName', '').replace('Edit', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        const permissions = [];
        $('input[name="group-flag-edit"]:checked').each(function () {
            if ($(this).val()) {
                permissions.push($(this).val());
            }
        });

        sendRequest({ update_access_group: true, group_id: $('#editGroupId').val(), name: name, permissions: permissions })
            .done(function (result) {
                if (result.status == 'success') {
                    location.reload();
                } else {
                    noty(result.message, result.status);
                }
            });
    });

    $(document).on('click', '.at-btn-group-delete', function () {
        const groupId = $(this).closest('tr').attr('data-group-id');
        const $row = $(this).closest('tr');

        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteAccessGroup'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({ delete_access_group: true, group_id: groupId })
                    .done(function (result) {
                        noty(result.message, result.status);
                        if (result.status == 'success') {
                            $row.remove();
                        }
                    });
            }
        });
    });

    $('#addAtoolsReason').on('click', function () {
        const name = {};
        var $btn = $(this);

        $('.at__settings-adding [id^="reasonName"]').each(function () {
            const lang = this.id.replace('reasonName', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        const type = $('input[name="reason-punish-type"]:checked').val() || '';

        sendRequestWithButton($btn, { create_reason: true, name: name, type: type })
            .done(function (result) {
                if (result.status == 'success') {
                    location.reload();
                } else {
                    noty(result.message, result.status);
                }
            });
    });

    $(document).on('click', '.at-btn-reason-edit', function () {
        const $row = $(this).closest('tr');
        const reasonData = $row.attr('data-reason');

        if (!reasonData) {
            return;
        }

        let reason;
        try {
            reason = JSON.parse(reasonData);
        } catch (error) {
            return;
        }

        $('#editReasonId').val($row.attr('data-reason-id'));

        $('#editReasonAtools [id^="reasonName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('reasonName', '').replace('Edit', '').toLowerCase();
            $(this).val(reason.name[lang] || '');
        });

        const $typeInput = $('input[name="reason-punish-type-edit"][value="' + reason.type + '"]');
        $('input[name="reason-punish-type-edit"]').prop('checked', false);
        if ($typeInput.length) {
            $typeInput.prop('checked', true).trigger('change');
        }

        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#reasonTypePunishEdit').closest('.adaptive-select-wrapper'));
        }
    });

    $('#saveAtoolsReasonEdit').on('click', function () {
        const name = {};

        $('#editReasonAtools [id^="reasonName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('reasonName', '').replace('Edit', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        sendRequest({
            update_reason: true,
            reason_id: $('#editReasonId').val(),
            name: name,
            type: $('input[name="reason-punish-type-edit"]:checked').val() || ''
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-reason-delete', function () {
        const reasonId = $(this).closest('tr').attr('data-reason-id');
        const $row = $(this).closest('tr');

        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteReason'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({ delete_reason: true, reason_id: reasonId })
                    .done(function (result) {
                        noty(result.message, result.status);
                        if (result.status == 'success') {
                            $row.remove();
                        }
                    });
            }
        });
    });

    $('#addAtoolsTerm').on('click', function () {
        const name = {};
        var $btn = $(this);

        $('.at__settings-adding [id^="termName"]').each(function () {
            const lang = this.id.replace('termName', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        sendRequestWithButton($btn, {
            create_term: true,
            name: name,
            time: parseInt($('#termValueAdd').val(), 10) || 0,
            type: $('input[name="term-type"]:checked').val() || ''
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-term-edit', function () {
        const $row = $(this).closest('tr');
        const termData = $row.attr('data-term');

        if (!termData) {
            return;
        }

        let term;
        try {
            term = JSON.parse(termData);
        } catch (error) {
            return;
        }

        $('#editTermId').val($row.attr('data-term-id'));

        $('#editTermAtools [id^="termName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('termName', '').replace('Edit', '').toLowerCase();
            $(this).val(term.name[lang] || '');
        });

        $('#termValueEdit').val(term.time);

        const $typeInput = $('input[name="term-type-edit"][value="' + term.type + '"]');
        $('input[name="term-type-edit"]').prop('checked', false);
        if ($typeInput.length) {
            $typeInput.prop('checked', true).trigger('change');
        }

        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($('#termTypeEdit').closest('.adaptive-select-wrapper'));
        }
    });

    $('#saveAtoolsTermEdit').on('click', function () {
        const name = {};

        $('#editTermAtools [id^="termName"][id$="Edit"]').each(function () {
            const lang = this.id.replace('termName', '').replace('Edit', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                name[lang] = String(value).trim();
            }
        });

        sendRequest({
            update_term: true,
            term_id: $('#editTermId').val(),
            name: name,
            time: parseInt($('#termValueEdit').val(), 10) || 0,
            type: $('input[name="term-type-edit"]:checked').val() || ''
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-term-delete', function () {
        const termId = $(this).closest('tr').attr('data-term-id');
        const $row = $(this).closest('tr');

        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteTerm'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({ delete_term: true, term_id: termId })
                    .done(function (result) {
                        noty(result.message, result.status);
                        if (result.status == 'success') {
                            $row.remove();
                        }
                    });
            }
        });
    });

    $('#addAtoolsVipGroup').on('click', function () {
        const display = {};
        var $btn = $(this);

        $('.at__settings-adding [id^="vipGroupDisplayAdd"]').each(function () {
            const lang = this.id.replace('vipGroupDisplayAdd', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                display[lang] = String(value).trim();
            }
        });

        sendRequestWithButton($btn, {
            create_vip_group: true,
            ini: ($('#vipGroupIniAdd').val() || '').trim(),
            display: display
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-vip-group-edit', function () {
        const $row = $(this).closest('tr');
        const groupData = $row.attr('data-vip-group');

        if (!groupData) {
            return;
        }

        let group;
        try {
            group = JSON.parse(groupData);
        } catch (error) {
            return;
        }

        $('#editVipGroupId').val($row.attr('data-vip-group-id'));
        $('#vipGroupIniEdit').val(group.ini || '');

        $('#editVipGroupAtools [id^="vipGroupDisplayEdit"]').each(function () {
            const lang = this.id.replace('vipGroupDisplayEdit', '').toLowerCase();
            $(this).val((group.display && group.display[lang]) || '');
        });
    });

    $('#saveAtoolsVipGroupEdit').on('click', function () {
        const display = {};

        $('#editVipGroupAtools [id^="vipGroupDisplayEdit"]').each(function () {
            const lang = this.id.replace('vipGroupDisplayEdit', '').toLowerCase();
            const value = $(this).val();

            if (value && String(value).trim() !== '') {
                display[lang] = String(value).trim();
            }
        });

        sendRequest({
            update_vip_group: true,
            vip_group_id: $('#editVipGroupId').val(),
            ini: ($('#vipGroupIniEdit').val() || '').trim(),
            display: display
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-vip-group-delete', function () {
        const groupId = $(this).closest('tr').attr('data-vip-group-id');
        const $row = $(this).closest('tr');

        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteVipGroup'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({ delete_vip_group: true, vip_group_id: groupId })
                    .done(function (result) {
                        noty(result.message, result.status);
                        if (result.status == 'success') {
                            $row.remove();
                        }
                    });
            }
        });
    });

    function collectAdminGroupFlags(selector) {
        const flags = [];

        $(selector + ':checked').each(function () {
            if ($(this).val()) {
                flags.push(String($(this).val()).toLowerCase());
            }
        });

        return flags.sort().join('');
    }

    function setAdminGroupFlags(selector, flags) {
        const set = {};
        String(flags || '').split('').forEach(function (flag) {
            if (flag) {
                set[flag.toLowerCase()] = true;
            }
        });

        $(selector).each(function () {
            $(this).prop('checked', !!set[String($(this).val()).toLowerCase()]);
        });
    }

    function toggleAdminGroupFlags(gameType, $root) {
        $root.find('.at-admin-group-flags-set').each(function () {
            $(this).toggle($(this).data('game') === gameType);
        });

        if (typeof updateAdaptiveSelectText === 'function') {
            updateAdaptiveSelectText($root.closest('.adaptive-select-wrapper'));
        }
    }

    $(document).on('change', 'input[name="admin-group-game"]', function () {
        toggleAdminGroupFlags($('input[name="admin-group-game"]:checked').val() || 'cs2', $('#adminGroupFlags'));
    });

    $('#addAtoolsAdminGroup').on('click', function () {
        var $btn = $(this);
        sendRequestWithButton($btn, {
            create_admin_server_group: true,
            type: $('input[name="admin-group-game"]:checked').val() || '',
            name: ($('#adminGroupNameAdd').val() || '').trim(),
            immunity: parseInt($('#adminGroupImmunityAdd').val(), 10) || 0,
            flags: collectAdminGroupFlags('input[name="admin-group-flag-add"]')
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-admin-server-group-edit', function () {
        const $row = $(this).closest('tr');
        const groupData = $row.attr('data-admin-server-group');

        if (!groupData) {
            return;
        }

        let group;
        try {
            group = JSON.parse(groupData);
        } catch (error) {
            return;
        }

        $('#editAdminGroupId').val($row.attr('data-admin-server-group-id'));
        $('#editAdminGroupType').val(group.type || $row.attr('data-admin-server-group-type') || '');
        $('#adminGroupNameEdit').val(group.name || '');
        $('#adminGroupImmunityEdit').val(group.immunity != null ? group.immunity : '');

        const gameType = group.type || $row.attr('data-admin-server-group-type') || 'csgo';

        toggleAdminGroupFlags(gameType, $('#adminGroupFlagsEdit'));
        setAdminGroupFlags('input[name="admin-group-flag-edit"]', group.flags || '');
    });

    $('#saveAtoolsAdminGroupEdit').on('click', function () {
        sendRequest({
            update_admin_server_group: true,
            type: $('#editAdminGroupType').val(),
            group_id: $('#editAdminGroupId').val(),
            name: ($('#adminGroupNameEdit').val() || '').trim(),
            immunity: parseInt($('#adminGroupImmunityEdit').val(), 10) || 0,
            flags: collectAdminGroupFlags('input[name="admin-group-flag-edit"]')
        }).done(function (result) {
            if (result.status == 'success') {
                location.reload();
            } else {
                noty(result.message, result.status);
            }
        });
    });

    $(document).on('click', '.at-btn-admin-server-group-delete', function () {
        const $row = $(this).closest('tr');

        openDialog({
            message: get_translate_module_phrase('module_page_atools', '_at_confirmDeleteAdminGroup'),
            confirmText: get_translate_phrase('_Delete_Action'),
            cancelText: get_translate_module_phrase('module_page_atools', '_at_no'),
            onConfirm: function () {
                sendRequest({
                    delete_admin_server_group: true,
                    type: $row.attr('data-admin-server-group-type'),
                    group_id: $row.attr('data-admin-server-group-id')
                }).done(function (result) {
                    noty(result.message, result.status);
                    if (result.status == 'success') {
                        $row.remove();
                    }
                });
            }
        });
    });
});