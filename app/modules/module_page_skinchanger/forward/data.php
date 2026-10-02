<?php

use app\modules\module_page_skinchanger\ext\Router;
use app\modules\module_page_skinchanger\ext\Controllers\ActionsController;
$ActionsController = new ActionsController($Db, $Translate, $General);
Router::controller($ActionsController);

Router::get('/categories', 'getCategories')->rateLimit('read');
Router::get('/weapons/{id}/skins', 'getSkins')->rateLimit('read');
Router::post('/skins/search', 'searchSkins')->rateLimit('search');
Router::get('/agents', 'getAgents')->rateLimit('read');
Router::get('/music', 'getMusic')->rateLimit('read');
Router::get('/coins', 'getCoins')->rateLimit('read');
Router::get('/collectibles', 'getCollectibles')->rateLimit('read');
Router::get('/collections', 'getPublicCollections')->rateLimit('search');
Router::get('/collections/{collection_id}', 'getCollectionDetail')->rateLimit('read');
Router::get('/presets', 'getDefaultCollections')->rateLimit('read');
Router::get('/presets/{preset_id}', 'getPresetDetail')->rateLimit('read');
Router::get('/player/init', 'getInitData')->middleware('auth')->rateLimit('read');
Router::get('/player/skins', 'getPlayerSkins')->middleware('auth')->rateLimit('read');
Router::get('/player/placeholders', 'getPlaceholders')->middleware('auth')->rateLimit('read');
Router::get('/player/collections', 'getPlayerCollections')->middleware('auth')->rateLimit('read');
Router::post('/player/skins', 'assignSkinFull')->middleware('auth')->rateLimit('write');
Router::put('/player/skins', 'updateSkinSettingsFull')->middleware('auth')->rateLimit('write');
Router::delete('/player/skins', 'removeSkinFull')->middleware('auth')->rateLimit('write');
Router::delete('/player/skins/batch', 'removeCheckedSkins')->middleware('auth')->rateLimit('write');
Router::delete('/player/skins/all', 'resetAll')->middleware('auth')->rateLimit('write');
Router::post('/player/items', 'assignItemFull')->middleware('auth')->rateLimit('write');
Router::delete('/player/items', 'removeItemFull')->middleware('auth')->rateLimit('write');
Router::post('/player/collections', 'createCollection')->middleware('auth')->rateLimit('collection');
Router::post('/player/collections/activate', 'activateCollection')->middleware('auth')->rateLimit('collection');
Router::put('/player/collections/{collection_id}', 'renameCollection')->middleware('auth')->rateLimit('collection');
Router::delete('/player/collections/{collection_id}', 'deleteCollection')->middleware('auth')->rateLimit('collection');
Router::put('/player/collections/{collection_id}/public', 'setCollectionPublic')->middleware('auth')->rateLimit('collection');
Router::post('/player/collections/{collection_id}/snapshot', 'saveCollectionSnapshot')->middleware('auth')->rateLimit('collection');
Router::post('/player/collections/{collection_id}/like', 'toggleCollectionLike')->middleware('auth')->rateLimit('like');
Router::post('/player/presets/{preset_id}/activate', 'activateDefaultCollection')->middleware('auth')->rateLimit('collection');
Router::get('/admin/cache', 'getCacheInfo')->middleware('admin')->rateLimit('read');
Router::post('/admin/cache', 'cacheUpdate')->middleware('admin')->rateLimit('write');
Router::get('/admin/settings', 'getSettingsInitData')->middleware('admin')->rateLimit('read');
Router::post('/admin/settings', 'saveCollectionSettings')->middleware('admin')->rateLimit('collection');
Router::get('/admin/tables', 'checkTablesInstalled')->middleware('admin')->rateLimit('read');
Router::post('/admin/tables', 'installTables')->middleware('admin')->rateLimit('collection');
Router::post('/admin/presets', 'copyAsDefault')->middleware('admin')->rateLimit('collection');
Router::delete('/admin/presets/{preset_id}', 'deletePreset')->middleware('admin')->rateLimit('collection');
Router::put('/admin/presets/reorder', 'reorderPresets')->middleware('admin')->rateLimit('collection');

$api      = 'skinchanger/api';
$matchUrl = $_SERVER['REQUEST_URI'] ?? '/';

if (strpos($matchUrl, $api) !== false) {
    $path    = rtrim(strtok($matchUrl, '?'), '/');
    $apiPath = substr($path, strpos($path, $api) + strlen($api));
    Router::dispatch($apiPath ?: '/', $_SERVER['REQUEST_METHOD'] ?? 'GET');
    exit;
}

$Router->map('GET|POST', 'skinchanger/[index|settings|community:page]/', 'page');
$Router->map('GET|POST', 'skinchanger/community/[i:col_id]/',           'page');

$Map  = $Router->match($matchUrl);
$page = $Map['params']['page'] ?? 'index';
if ($page === 'community') $page = 'index';
if (isset($Map['params']['col_id'])) $page = 'index';

$hasVipAccess = isset($_SESSION['steamid']) && !empty($_SESSION['steamid'])
    ? $ActionsController->SkinchangerController->hasVipAccess()
    : false;

$vipAccessGroupsList = '';
if (!$hasVipAccess) {
    $scSettings = $ActionsController->SkinchangerController->getCollectionSettings();
    $groups = array_filter(array_map('trim', explode(';', $scSettings['vip_access_groups'] ?? '')));
    $vipAccessGroupsList = implode(', ', $groups);
}


$Modules->set_page_title($Translate->get_translate_module_phrase('module_page_skinchanger', '_set_page_title') . " | " . $General->arr_general['short_name']);
$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_skinchanger', '_set_page_description'));