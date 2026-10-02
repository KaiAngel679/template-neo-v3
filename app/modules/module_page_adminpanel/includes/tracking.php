<div class="col-md-6">
    <div class="card height-100">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingModuleSettings') ?></h5>
        </div>
        <div class="card-container height-100">
            <form id="module_settings_form" enctype="multipart/form-data" method="post">

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="module_enabled" name="module_enabled">
                    <label for="module_enabled"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingEnableModule') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="ignore_unauthorized" name="ignore_unauthorized">
                    <label for="ignore_unauthorized"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingIgnoreUnauthorized') ?></label>
                </div>

                <hr>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_page_folding" name="track_page_folding">
                    <label for="track_page_folding"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackPageFolding') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_clicks" name="track_clicks">
                    <label for="track_clicks"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackClicks') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_focus" name="track_focus">
                    <label for="track_focus"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackFocus') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_input" name="track_input">
                    <label for="track_input"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackInput') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_hover" name="track_hover">
                    <label for="track_hover"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackHover') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_devtools" name="track_devtools">
                    <label for="track_devtools"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackDevTools') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_scroll" name="track_scroll">
                    <label for="track_scroll"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackScroll') ?></label>
                </div>

                <div class="inputs-inline">
                    <input class="switch" type="checkbox" id="track_touch" name="track_touch">
                    <label for="track_touch"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingTrackTouch') ?></label>
                </div>

                <div class="inputs-inline">
                    <label for="hover_delay"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingHoverDelay') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="hover_delay-select">
                            <li>
                                <label class="adaptive-select__label" for="hover_500ms">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking05Seconds') ?></div>
                                    <input class="hide-input" id="hover_500ms" type="radio" name="hover_delay" value="0.5">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="hover_1000ms">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking1Second') ?></div>
                                    <input class="hide-input" id="hover_1000ms" type="radio" name="hover_delay" value="1">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="hover_2000ms">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking2Seconds') ?></div>
                                    <input class="hide-input" id="hover_2000ms" type="radio" name="hover_delay" value="2">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="hover_3000ms">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking3Seconds') ?></div>
                                    <input class="hide-input" id="hover_3000ms" type="radio" name="hover_delay" value="3">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="hover_5000ms">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking5Seconds') ?></div>
                                    <input class="hide-input" id="hover_5000ms" type="radio" name="hover_delay" value="5">
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="hover_delay-select">
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking3Seconds') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <hr>

                <div class="flex-inline">

                
                <div class="inputs-inline">
                    <label for="session_duration"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingSessionDuration') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="session_duration-select">
                            <li>
                                <label class="adaptive-select__label" for="session_10min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking10Minutes') ?></div>
                                    <input class="hide-input" id="session_10min" type="radio" name="session_duration" value="600">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="session_20min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking20Minutes') ?></div>
                                    <input class="hide-input" id="session_20min" type="radio" name="session_duration" value="1200">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="session_30min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking30Minutes') ?></div>
                                    <input class="hide-input" id="session_30min" type="radio" name="session_duration" value="1800">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="session_40min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking40Minutes') ?></div>
                                    <input class="hide-input" id="session_40min" type="radio" name="session_duration" value="2400">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="session_50min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking50Minutes') ?></div>
                                    <input class="hide-input" id="session_50min" type="radio" name="session_duration" value="3000">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="session_1hour">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking1Hour') ?></div>
                                    <input class="hide-input" id="session_1hour" type="radio" name="session_duration" value="3600">
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="session_duration-select">
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking30Minutes') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                <div class="inputs-inline">
                    <label for="max_inactive_time"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingMaxInactiveTime') ?></label>
                    <div class="adaptive-select-wrapper">
                        <ul class="adaptive-select__dropdown-list" id="max_inactive_time-select">
                            <li>
                                <label class="adaptive-select__label" for="inactive_2min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking2Minutes') ?></div>
                                    <input class="hide-input" id="inactive_2min" type="radio" name="max_inactive_time" value="120">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="inactive_3min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking3Minutes') ?></div>
                                    <input class="hide-input" id="inactive_3min" type="radio" name="max_inactive_time" value="180">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="inactive_5min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking5Minutes') ?></div>
                                    <input class="hide-input" id="inactive_5min" type="radio" name="max_inactive_time" value="300">
                                </label>
                            </li>
                            <li>
                                <label class="adaptive-select__label" for="inactive_10min">
                                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking10MinutesInactive') ?></div>
                                    <input class="hide-input" id="inactive_10min" type="radio" name="max_inactive_time" value="600">
                                </label>
                            </li>
                        </ul>
                        <div class="adaptive-select" open-select="max_inactive_time-select">
                            <span class="adaptive-select__span_text"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTracking3Minutes') ?></span>
                            <span class="margin-left-auto adaptive-select__arrow">
                                <svg>
                                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                                </svg>
                            </span>
                        </div>
                    </div>
                </div>

                </div>

            </form>
        </div>
        <div class="card-bottom">
            <button class="width-100" type="submit" form="module_settings_form" id="save_settings_btn"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingSaveSettings') ?></button>
        </div>
    </div>
</div>

<div class="col-md-6">
    <div class="card" style="height: 100%; display: flex; flex-direction: column;">
        <div class="card-header">
            <h5 class="badge"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingAccessManagement') ?></h5>
        </div>

        <div class="card-container" style="flex: 1; min-height: 0;">
            
            <div class="inputs-inline" style="margin-bottom: 1rem;">
                <label for="steam_id"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingSteamID') ?></label>
                <div class="flex-inline" style="gap: 0.5rem;">
                    <input type="text" name="steam_id" id="steam_id"
                           placeholder="<?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingSteamIDPlaceholder') ?>" style="flex: 1;">
                    <button type="button" id="add_user_btn" style="white-space: nowrap;"><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingAddUser') ?></button>
                </div>
            </div>

            <div id="table_container" class="table-responsive" style="max-height: 430px; overflow-y: auto; position: relative;">
                <div id="table_skeleton" class="loader" style="
                    height: 430px;
                    width: 100%;
                    border-radius: 0;
                "></div>
                
                <table class="table" id="real_table" style="display: none;">
                    <thead>
                        <tr>
                            <th><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingSteamID64') ?></th>
                            <th><?= $Translate->get_translate_module_phrase('module_block_utracking', '_UTrackingAddDate') ?></th>
                            <th></th>
                        </tr>
                    </thead>

                    <tbody id="users_table">
                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const accessApiBase = '/tracking/api/access';
    const settingsApiBase = '/tracking/api/settings';
    
    const addBtn = document.getElementById('add_user_btn');
    const steamInput = document.getElementById('steam_id');
    const usersTable = document.getElementById('users_table');
    const tableSkeleton = document.getElementById('table_skeleton');
    const realTable = document.getElementById('real_table');
    
    const saveSettingsBtn = document.getElementById('save_settings_btn');
    const settingsForm = document.getElementById('module_settings_form');
    
    loadSettings();
    loadUsers();
    
    addBtn.addEventListener('click', async function() {
        const steamId = steamInput.value.trim();
        if (!steamId) {
            return;
        }
        
        if (steamId.length < 17 || isNaN(steamId)) {
            return;
        }
        
        tableSkeleton.style.display = 'block';
        realTable.style.display = 'none';
        
        try {
            const response = await fetch(accessApiBase + '/add/', {
                method: 'POST',
                body: new URLSearchParams({ 'steam_id': steamId }),
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                }
            });
            
            const result = await response.json();
            
            if (result.status === 'success') {
                steamInput.value = '';
                loadUsers();
            } else {
                tableSkeleton.style.display = 'none';
                realTable.style.display = 'table';
            }
        } catch (error) {
            console.error('Error:', error);
            tableSkeleton.style.display = 'none';
            realTable.style.display = 'table';
        }
    });
    
    async function loadUsers() {
        tableSkeleton.style.display = 'block';
        realTable.style.display = 'none';
        
        try {
            const response = await fetch(accessApiBase + '/list/');
            const result = await response.json();
            
            tableSkeleton.style.display = 'none';
            realTable.style.display = 'table';
            
            if (result.users && result.users.length > 0) {
                let html = '';
                result.users.forEach(user => {
                    const date = user.created_at ? 
                        new Date(user.created_at * 1000).toLocaleDateString('ru-RU', {
                            day: '2-digit',
                            month: '2-digit',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }) : '-';
                    
                    html += `
                        <tr>
                            <td>${user.steam_id_64}</td>
                            <td>${date}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="button-delete" onclick="deleteUser('${user.steam_id_64}', this)">
                                        ${get_translate_module_phrase('module_block_utracking', '_UTrackingDelete')}
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                });
                
                usersTable.innerHTML = html;
            } else {
                usersTable.innerHTML = `
                    <tr>
                        <td colspan="3" style="text-align: center; padding: 20px;">
                            ${get_translate_module_phrase('module_block_utracking', '_UTrackingNoUsers')}
                        </td>
                    </tr>
                `;
            }
        } catch (error) {
            console.error('Error loading users:', error);
            tableSkeleton.style.display = 'none';
            realTable.style.display = 'table';
            usersTable.innerHTML = `
                <tr>
                    <td colspan="3" style="text-align: center; padding: 20px; color: var(--error-color);">
                        ${get_translate_module_phrase('module_block_utracking', '_UTrackingLoadingError')}
                    </td>
                </tr>
            `;
        }
    }
    
    window.deleteUser = async function(steamId, button) {
        const response = await fetch(accessApiBase + '/delete/', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ steam_id: steamId })
        });
        
        const result = await response.json();
        
        if (result.status === 'success') {
            button.closest('tr').remove();
            
            if (usersTable.querySelectorAll('tr').length === 1) {
                loadUsers();
            }
        }
    }
    
    settingsForm.addEventListener('submit', function(e) {
        e.preventDefault(); 
        saveSettings();
    });
    
    saveSettingsBtn.addEventListener('click', function(e) {
        e.preventDefault(); 
        saveSettings();
    });
    
    async function saveSettings() {
        const originalText = get_translate_module_phrase('module_block_utracking', '_UTrackingSaveSettings');
        saveSettingsBtn.textContent = get_translate_module_phrase('module_block_utracking', '_UTrackingSaving');
        saveSettingsBtn.disabled = true;
        
        try {
            const data = {};
            
            const checkboxes = [
                'module_enabled',
                'ignore_unauthorized',
                'track_page_folding',
                'track_clicks',
                'track_focus',
                'track_input',
                'track_hover',
                'track_devtools',
                'track_scroll',
                'track_touch'
            ];
            
            checkboxes.forEach(checkboxId => {
                const checkbox = document.getElementById(checkboxId);
                data[checkboxId] = checkbox.checked;
            });
            
            const hoverDelayRadio = document.querySelector('input[name="hover_delay"]:checked');
            if (hoverDelayRadio) {
                data.hover_delay = parseFloat(hoverDelayRadio.value);
            }
            
            const sessionDurationRadio = document.querySelector('input[name="session_duration"]:checked');
            if (sessionDurationRadio) {
                data.session_duration = parseInt(sessionDurationRadio.value);
            }
            
            const maxInactiveTimeRadio = document.querySelector('input[name="max_inactive_time"]:checked');
            if (maxInactiveTimeRadio) {
                data.max_inactive_time = parseInt(maxInactiveTimeRadio.value);
            }
            
            const response = await fetch(settingsApiBase + '/update/', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            });
        } catch (error) {
            console.error('Error:', error);
        } finally {
            saveSettingsBtn.textContent = originalText;
            saveSettingsBtn.disabled = false;
        }
    }
    
    async function loadSettings() {
        try {
            const response = await fetch(settingsApiBase + '/');
            const result = await response.json();
        
            
            if (result.status === 'success') {
                const settings = result.settings;

                document.getElementById('module_enabled').checked = settings.module_enabled || false;
                document.getElementById('ignore_unauthorized').checked = settings.ignore_unauthorized || false;
                document.getElementById('track_page_folding').checked = settings.track_page_folding || false;
                document.getElementById('track_clicks').checked = settings.track_clicks || false;
                document.getElementById('track_focus').checked = settings.track_focus || false;
                document.getElementById('track_input').checked = settings.track_input || false;
                document.getElementById('track_hover').checked = settings.track_hover || false;
                document.getElementById('track_devtools').checked = settings.track_devtools || false;
                document.getElementById('track_scroll').checked = settings.track_scroll || false;
                document.getElementById('track_touch').checked = settings.track_touch || false;
                
                if (settings.hover_delay !== undefined) {
                    const hoverDelayValue = settings.hover_delay.toString();
                    const hoverRadio = document.querySelector(`input[name="hover_delay"][value="${hoverDelayValue}"]`);
                    if (hoverRadio) {
                        hoverRadio.checked = true;
                        const hoverSelectSpan = document.querySelector('.adaptive-select[open-select="hover_delay-select"] .adaptive-select__span_text');
                        if (hoverSelectSpan) {
                            hoverSelectSpan.textContent = getHoverDelayLabel(hoverDelayValue);
                        }
                    }
                }
                
                if (settings.session_duration !== undefined) {
                    const sessionDurationValue = settings.session_duration.toString();
                    const sessionRadio = document.querySelector(`input[name="session_duration"][value="${sessionDurationValue}"]`);
                    if (sessionRadio) {
                        sessionRadio.checked = true;
                        const sessionSelectSpan = document.querySelector('.adaptive-select[open-select="session_duration-select"] .adaptive-select__span_text');
                        if (sessionSelectSpan) {
                            sessionSelectSpan.textContent = getSessionDurationLabel(sessionDurationValue);
                        }
                    }
                }
                
                if (settings.max_inactive_time !== undefined) {
                    const inactiveTimeValue = settings.max_inactive_time.toString();
                    const inactiveRadio = document.querySelector(`input[name="max_inactive_time"][value="${inactiveTimeValue}"]`);
                    if (inactiveRadio) {
                        inactiveRadio.checked = true;
                        const inactiveSelectSpan = document.querySelector('.adaptive-select[open-select="max_inactive_time-select"] .adaptive-select__span_text');
                        if (inactiveSelectSpan) {
                            inactiveSelectSpan.textContent = getInactiveTimeLabel(inactiveTimeValue);
                        }
                    }
                }
            } else {
                
            }
        } catch (error) {
            
        }
    }
    
    function getHoverDelayLabel(value) {
        const labels = {
            '0.5': get_translate_module_phrase('module_block_utracking', '_UTracking05Seconds'),
            '1': get_translate_module_phrase('module_block_utracking', '_UTracking1Second'),
            '2': get_translate_module_phrase('module_block_utracking', '_UTracking2Seconds'),
            '3': get_translate_module_phrase('module_block_utracking', '_UTracking3Seconds'),
            '5': get_translate_module_phrase('module_block_utracking', '_UTracking5Seconds')
        };
        return labels[value] || get_translate_module_phrase('module_block_utracking', '_UTracking3Seconds');
    }
    
    function getSessionDurationLabel(value) {
        const labels = {
            '600': get_translate_module_phrase('module_block_utracking', '_UTracking10Minutes'),
            '1200': get_translate_module_phrase('module_block_utracking', '_UTracking20Minutes'),
            '1800': get_translate_module_phrase('module_block_utracking', '_UTracking30Minutes'),
            '2400': get_translate_module_phrase('module_block_utracking', '_UTracking40Minutes'),
            '3000': get_translate_module_phrase('module_block_utracking', '_UTracking50Minutes'),
            '3600': get_translate_module_phrase('module_block_utracking', '_UTracking1Hour')
        };
        return labels[value] || get_translate_module_phrase('module_block_utracking', '_UTracking30Minutes');
    }
    
    function getInactiveTimeLabel(value) {
        const labels = {
            '120': get_translate_module_phrase('module_block_utracking', '_UTracking2Minutes'),
            '180': get_translate_module_phrase('module_block_utracking', '_UTracking3Minutes'),
            '300': get_translate_module_phrase('module_block_utracking', '_UTracking5Minutes'),
            '600': get_translate_module_phrase('module_block_utracking', '_UTracking10MinutesInactive')
        };
        return labels[value] || get_translate_module_phrase('module_block_utracking', '_UTracking3Minutes');
    }
});
</script>


<style>
.loader {
    position: relative;
    background: var(--input-form);
    overflow: hidden;
    height: 53px;
    border-radius: var(--br-12);
    z-index: 2;
    width: 100%;
}

.loader::after {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg,
      rgba(255, 255, 255, 0) 0%,
      var(--transparent-2-w),
      rgba(255, 255, 255, 0) 100%);
  animation: shimmer 1.5s infinite;
}
</style>