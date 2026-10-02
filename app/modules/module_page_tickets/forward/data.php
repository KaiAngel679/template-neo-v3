<?php

use app\modules\module_page_tickets\ext\Controllers\AccessController;
use app\modules\module_page_tickets\ext\Controllers\BlocksController;
use app\modules\module_page_tickets\ext\Controllers\SendController;
use app\modules\module_page_tickets\ext\Controllers\ListController;
use app\modules\module_page_tickets\ext\Controllers\ArchiveController;
use app\modules\module_page_tickets\ext\Controllers\ChatController;
use app\modules\module_page_tickets\ext\Controllers\SettingsController;
use app\modules\module_page_tickets\ext\Repositories\AccessRepository;
use app\modules\module_page_tickets\ext\Repositories\AdditionalRepository;
use app\modules\module_page_tickets\ext\Repositories\ChatRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;
use app\modules\module_page_tickets\ext\Repositories\FilepondRepository;
use app\modules\module_page_tickets\ext\Services\DatabaseService;

if (IN_LR != true) {
    header_fix($General->arr_general['site']);
    exit;
}

$Router->map('GET|POST|DELETE', 'tickets/[:page]/[:id]?/', 'tickets');
$Map = $Router->match();
$page = $Map['params']['page'] ?? 'send';
$id = $Map['params']['id'] ?? null;

$ds = new DatabaseService($Db);
$fr = new FilepondRepository();
$jr = new JsonRepository();

if ($ds->checkTables()) {
    if (!in_array($page, ['list', 'access', 'settings', 'send', 'chat', 'blocks', 'archive'])) {
        get_iframe(404, $Translate->get_translate_phrase('_Page_not_found')) && die();
    }
    if ($page !== 'send' && !isset($_SESSION['steamid64'])) {
        get_iframe(401, $Translate->get_translate_module_phrase('module_page_tickets', '_notLogin')) && die();
    }
    $access = $ds->getAccessFullCheck($_SESSION['steamid64']);
    if ($page != 'send') {
        switch ($page) {
            case 'list':
                $lc = new ListController($Db, $Translate, $General);
                $categories = $access['category'] ? array_map('trim', explode(';', $access['category'])) : [];
                if (isset($_POST['render_list'])) {
                    exit(json_encode($lc->constructList($_POST['category'], $_POST['server'], $_POST['limit'], $_POST['search'], $_POST['my'], $_POST['page']), true));
                }
                if (isset($_POST['close_ticket'])) {
                    $steamTicket = $ds->getInfoUserSteam($_POST['ticket_id']);
                    if ($access['access'] || $steamTicket == $_SESSION['steamid64']) {
                        exit(json_encode($lc->closeTicket($_POST['ticket_id']), true));
                    }
                }
                if ($access['access']) {
                    if (isset($_POST['get_categories'])) {
                        exit(json_encode($lc->getCategory(), true));
                    } elseif (isset($_POST['move_ticket'])) {
                        exit(json_encode($lc->moveTicket($_POST['ticket_id'], $_POST['new_category_id']), true));
                    }
                    if ($access['add_delete']) {
                        if (isset($_POST['delete_ticket'])) {
                            exit(json_encode($lc->deleteTicket($_POST['ticket_id']), true));
                        }
                    }
                }
                break;
            case 'archive':
                $ac = new ArchiveController($Db, $Translate, $General);
                $categories = $access['category'] ? array_map('trim', explode(';', $access['category'])) : [];
                if (isset($_POST['render_archive'])) {
                    exit(json_encode($ac->constructArchive($_POST['category'], $_POST['server'], $_POST['limit'], $_POST['search'], $_POST['my'], $_POST['page']), true));
                }
                if ($access['access']) {
                    if (isset($_POST['open_ticket'])) {
                        exit(json_encode($ac->openTicket($_POST['ticket_id']), true));
                    }
                    if ($access['add_delete']) {
                        if (isset($_POST['delete_ticket'])) {
                            exit(json_encode($ac->deleteTicket($_POST['ticket_id']), true));
                        }
                    }
                }
                break;
            case 'chat':
                $cc = new ChatController($Db, $Translate, $Notifications, $General, $id);
                $cr = new ChatRepository;
                if ($id) {
                    if (is_numeric($id)) {
                        $chatXuesos = $ds->getInfoChat($id);

                        if (!$access['access']) {
                            if (!$ds->getUserCheckChat($_SESSION['steamid64'], $id)) {
                                get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_notYourTicket')) && die();
                            }
                        } else {
                            if ($access['category']) {
                                $ticketCategoryId = $chatXuesos['category_id'];
                                $allowedCategories = $access['category'] ? array_map('trim', explode(';', $access['category'])) : [];

                                if (!in_array($ticketCategoryId, $allowedCategories)) {
                                    get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_haventAccess')) && die();
                                }
                            }
                        }

                        if (isset($_POST['load_messages'])) {
                            exit(json_encode($cc->getMessages($_POST['last_message_id']), true));
                        } elseif (isset($_POST['send_message'])) {
                            exit(json_encode($cc->sendMessage($_POST['message'], $_POST['hide'], $_POST['file']), true));
                        } elseif (isset($_POST['close_ticket'])) {
                            exit(json_encode($cc->closeTicket(), true));
                        }

                        if ($access['access']) {
                            if (isset($_POST['slow_mode'])) {
                                exit(json_encode($cc->slowMode(), true));
                            } elseif (isset($_POST['delete_message'])) {
                                exit(json_encode($cc->deleteMessage($_POST['message_id']), true));
                            }
                        }
                    } else {
                        get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')) && die();
                    }
                } else {
                    get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_ticketHasNoID')) && die();
                }
                break;
            case 'access':
                if ($access['add_access']) {
                    $ac = new AccessController($Db, $Translate);
                    $ar = new AccessRepository;
                    if (isset($_POST['send_access'])) {
                        exit(json_encode($ac->createNewAccess($_POST['steam'], $_POST['add_access'], $_POST['add_block'], $_POST['add_delete'], $_POST['add_settings'], $_POST['general'], $_POST['category']), true));
                    } elseif (isset($_POST['delete_access'])) {
                        exit(json_encode($ac->deleteAccess($_POST['steam']), true));
                    } elseif (isset($_POST['get_access_edit'])) {
                        exit(json_encode($ac->getAccessUser($_POST['steam']), true));
                    } elseif (isset($_POST['edit_access'])) {
                        exit(json_encode($ac->editAcceessUser($_POST['steam'], $_POST['delete'], $_POST['block'], $_POST['add_access'], $_POST['settings'], $_POST['general'], $_POST['category']), true));
                    }
                } else {
                    get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_haventAccess')) && die();
                }
                break;
            case 'blocks':
                if ($access['add_block']) {
                    $bc = new BlocksController($Db, $Translate);
                    if (isset($_POST['send_block'])) {
                        exit(json_encode($bc->createNewBlock($_POST['steam'], $_POST['reason'], $_POST['time']), true));
                    } elseif (isset($_POST['delete_block'])) {
                        exit(json_encode($bc->deleteBlock($_POST['steam']), true));
                    }
                } else {
                    get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_haventAccess')) && die();
                }
                break;
            case 'settings':
                if ($access['add_settings']) {
                    $sc = new SettingsController($Db, $Translate);
                    $settings = $jr->getCache('noti');
                    $newSettings = $jr->getCache('settings');
                    if (isset($_POST['new_category'])) {
                        exit(json_encode($sc->createNewCategory($_POST['type'], $_POST['category'], $_POST['questions'], $_POST['description'], $_POST['servers'], $_POST['server_on'], $_POST['playtime'], $_POST['response_time'], $_POST['replay_time'], $_POST['amount_money'], $_POST['sort']), true));
                    } elseif (isset($_POST['new_answer'])) {
                        exit(json_encode($sc->createNewAnswer($_POST['answer'], $_POST['button']), true));
                    } elseif (isset($_POST['save_settings'])) {
                        exit(json_encode($sc->putSettings($_POST['noty'], $_POST['url'], $_POST['color'], $_POST['img']), true));
                    } elseif (isset($_POST['save_new_settings'])) {
                        exit(json_encode($sc->putSettingsNew($_POST['slow'], $_POST['slow_time'], $_POST['auto_close'], $_POST['duration']), true));
                    } elseif (isset($_POST['delete_category'])) {
                        exit(json_encode($sc->deleteCategory($_POST['id']), true));
                    } elseif (isset($_POST['delete_answer'])) {
                        exit(json_encode($sc->deleteAnswer($_POST['id']), true));
                    } elseif (isset($_POST['get_answer_edit'])) {
                        exit(json_encode($sc->getAnswerId($_POST['id']), true));
                    } elseif (isset($_POST['edit_answer'])) {
                        exit(json_encode($sc->editAnswer($_POST['id'], $_POST['button'], $_POST['text']), true));
                    } elseif (isset($_POST['get_category_edit'])) {
                        exit(json_encode($sc->getCategory($_POST['id']), true));
                    } elseif (isset($_POST['edit_category'])) {
                        exit(json_encode($sc->editCategory($_POST['id'], $_POST['title'], $_POST['description'], $_POST['server_on'], $_POST['servers'], $_POST['playtime'], $_POST['response_time'], $_POST['replay_time'], $_POST['amount_money'], $_POST['type'], $_POST['questions'], $_POST['sort']), true));
                    }
                } else {
                    get_iframe(403, $Translate->get_translate_module_phrase('module_page_tickets', '_haventAccess')) && die();
                }
                break;
        }
    } else {
        $sc = new SendController($Db, $Translate, $Notifications, $General);
        $ar = new AdditionalRepository($Translate);
        $categories = $ds->getCategoriesTabs();
        $responseTimes = [];
        $responseTimesFormat = [];
        foreach ($categories as $key) {
            if (isset($key['response_time']) && $key['response_time'] !== null) {
                $time = (int)$key['response_time'];
            } else {
                $time = $sc->getTimeRespons($key['id']);
            }
            if (!empty($time)) {
                $responseTimesFormat[$key['id']] = $ar->formatTicketTime($time);
                $responseTimes[$key['id']] = $time;
            } else {
                $responseTimesFormat[$key['id']] = $Translate->get_translate_module_phrase('module_page_tickets', '_Unknown');
                $responseTimes[$key['id']] = null;
            }
        }
        $fastestCategoryId = !empty(array_filter($responseTimes, fn($v) => $v !== null)) ? array_search(min(array_filter($responseTimes, fn($v) => $v !== null)), $responseTimes) : null;
        if (isset($_POST['send_category'])) {
            exit(json_encode($sc->constructFormCategory($_POST['category_id']), true));
        } elseif (isset($_POST['send_form'])) {
            exit(json_encode($sc->sendForm($_POST['category'], $_POST['topic'], $_POST['message'], $_POST['questions'], $_POST['file'], $_POST['server']), true));
        }
    }
    if (isset($_POST['send_filepond'])) {
        exit(json_encode($fr->sendPhoto($_FILES['filepond']), true));
    } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        parse_str(file_get_contents("php://input"), $data);
        exit(json_encode($fr->removePhoto($data['file']), true));
    }
} else {
    if (isset($_SESSION['user_admin'])) {
        $ds->createTables();
    } else {
        get_iframe(503, $Translate->get_translate_module_phrase('module_page_tickets', '_notConfigurated')) && die();
    }
}

$Modules->set_page_title($Translate->get_translate_module_phrase('module_page_tickets', '_tickets') . " | " . $General->arr_general['short_name']);
$Modules->set_page_description($Translate->get_translate_module_phrase('module_page_tickets', '_description'));
