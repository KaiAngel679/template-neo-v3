<script src="/app/modules/module_page_admintime/assets/js/vanilla-calendar.min.js?<?= time() ?>" defer></script>
<script src="/app/modules/module_page_admintime/assets/js/apexcharts.js?<?= time() ?>" defer></script>
<div class="row">
  <div class="col-md-9">
    <div class="card">
      <div class="card-header">
        <div class="badge"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_AdmOnline') ?></div>
      </div>
      <div class="card-container">
        <div class="admin-time__cards-wrapper" id="AdminTimeList">
        </div>
        <div id="AdminTimePagination"></div>
      </div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card height-100">
      <div class="card-header">
        <div class="badge"><?= $Translate->get_translate_phrase('_filter') ?></div>
      </div>
      <div class="card-container height-100">
        <div class="filter_adm_online height-100">
          <div id="VanillaCalendar"></div>
          <div class="input-form">
            <label><?= $Translate->get_translate_phrase('_selectServer') ?></label>
            <div class="adaptive-select-wrapper">
              <ul class="adaptive-select__dropdown-list" id="option-server_id-select">
                <li>
                  <label class="adaptive-select__label" for="server_id_all">
                    <div class="adaptive-select__label-text"><?= $Translate->get_translate_phrase('_allServers') ?></div>
                    <input class="hide-input" id="server_id_all" type="radio" value="all" name="server_id" checked onclick="getAdminTimeList()">
                  </label>
                </li>
                <?php foreach ($servers as $key => $server): ?>
                  <li>
                    <label class="adaptive-select__label" for="server_id_<?= $key ?>">
                      <div class="adaptive-select__label-text"><?= htmlentities($server['name']) ?></div>
                      <input class="hide-input" id="server_id_<?= $key ?>" type="radio" value="<?= $server['id'] ?>" name="server_id" onclick="getAdminTimeList()">
                    </label>
                  </li>
                <?php endforeach ?>
              </ul>
              <div class="adaptive-select" open-select="option-server_id-select">
                <span class="adaptive-select__span_text"><?= $Translate->get_translate_phrase('_allServers') ?></span>
                <span class="margin-left-auto adaptive-select__arrow">
                  <svg>
                    <use href="/resources/img/sprite.svg#chevron-down"></use>
                  </svg>
                </span>
              </div>
            </div>
          </div>
          <div class="input-form">
            <label for="admin_steamid"><?= $Translate->get_translate_module_phrase('module_page_admintime', '_findAdmin') ?></label>
            <input id="admin_steamid" name="admin_steamid" type="text" placeholder="<?= $Translate->get_translate_module_phrase('module_page_admintime', '_searchAdminBy') ?> SteamID64">
          </div>
          <input type="hidden" name="date_start" id="dateStart">
          <input type="hidden" name="date_end" id="dateEnd">
        </div>
      </div>
    </div>
  </div>
  <div class="popup_modal" id="sessions">
    <div class="popup_modal_content no-close no-scrollbar">
      <div class="popup_modal_head">
        <span id="adminName"></span>
        <div class="popup_modal_close">
          <svg>
            <use href="/resources/img/sprite.svg#x"></use>
          </svg>
        </div>
      </div>
      <div class="popup_sessions_body">
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th><?= $Translate->get_translate_phrase('_Server') ?></th>
                <th><?= $Translate->get_translate_module_phrase('module_page_admintime', '_joinTime') ?></th>
                <th><?= $Translate->get_translate_module_phrase('module_page_admintime', '_leaveTime') ?></th>
                <th><?= $Translate->get_translate_module_phrase('module_page_admintime', '_played') ?></th>
              </tr>
            </thead>
            <tbody id="sessions_list"></tbody>
          </table>
        </div>
        <div id="sessions_pagination"></div>
      </div>
    </div>
  </div>
  <div class="popup_modal" id="charts">
    <div class="popup_modal_content no-close no-scrollbar" style="min-width: 850px">
      <div class="popup_modal_head">
        <span id="adminNameCharts"></span>
        <div class="popup_modal_close">
          <svg>
            <use href="/resources/img/sprite.svg#x"></use>
          </svg>
        </div>
      </div>
      <div class="popup_sessions_body">
        <div id="chart"></div>
      </div>
    </div>
  </div>
</div>