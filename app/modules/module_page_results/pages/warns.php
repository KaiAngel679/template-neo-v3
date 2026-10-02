<div class="col-md-12">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_admin') ?></th>
                    <th><?= $Translate->get_translate_phrase('_Reason') ?></th>
                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_issued') ?></th>
                    <th><?= $Translate->get_translate_module_phrase('module_page_results', '_expires') ?></th>
                </tr>
            </thead>
            <tbody id="WarnsTableBody"></tbody>
        </table>
    </div>
    <div id="Pagination"></div>
</div>