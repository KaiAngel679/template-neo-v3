<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\ChatRepository;
use app\modules\module_page_tickets\ext\Repositories\DiscordRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;

class ArchiveController
{
    protected $ds, $cr, $jr, $dr, $Translate, $General;

    public function __construct($Db, $Translate, $General)
    {
        $this->ds = new DatabaseService($Db);
        $this->cr = new ChatRepository;
        $this->jr = new JsonRepository;
        $this->dr = new DiscordRepository($General);
        $this->Translate = $Translate;
        $this->General = $General;
    }

    public function constructArchive($category, $server, $limit, $search, $my, $page)
    {
        if (!is_numeric($limit) || !is_numeric($page)) {
            return ['status' => 'error'];
        }

        $offset = ($page - 1) * $limit;
        $access = $this->ds->getAccessFullCheck($_SESSION['steamid64']);
        $admin = !empty($access['access']);
        $delete = !empty($access['add_delete']) ? 1 : 0;

        $translate = ['ticket' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticket'), 'noTickets' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_noTickets'), 'closedBy' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_closedBy')];

        if ($admin) {
            $query = $this->ds->getTicketArchiveAdmin($category, $server, $my ? 1000000 : $limit, $search, $my ? 0 : $offset);
            $translate['open'] = $this->Translate->get_translate_module_phrase('module_page_tickets', '_open');
            $translate['delete'] = $this->Translate->get_translate_phrase('_Delete_Action');
        } else {
            $query = $this->ds->getTicketArchive($_SESSION['steamid64'], $my ? 1000000 : $limit, $my ? 0 : $offset);
        }

        $tickets = $query['tickets'] ?? [];

        if ($my) {
            $tickets = array_filter($tickets, function ($ticket) {
                $check = $this->cr->getMessagesChat($ticket['id'], null, true, $_SESSION['steamid64']);
                return !empty($check['hasUserMessage']);
            });

            $tickets = array_values($tickets);
            $total = count($tickets);
            $tickets = array_slice($tickets, $offset, $limit);
        } else {
            $total = (int)($query['total'] ?? count($tickets));
        }

        foreach ($tickets as &$ticket) {
            $ticket['date'] = date('d.m.Y, H:i', $ticket['created_at']);
            $ticket['date_close'] = isset($ticket['edit_at']) ? date('d.m.Y, H:i', $ticket['edit_at']) : '';
            $ticket['name'] = $this->General->checkName($ticket['steamid_close'] ?? null);
            $messages = $this->cr->getMessagesChat($ticket['id']);
            $ticket['first_message'] = $messages['messages'][0]['text'] ?? '';
            $ticket['avatar'] = $this->General->getAvatar($ticket['steamid'], 3);
            $ticket['title'] = $this->ds->getCategoryTitle($ticket['category_id']) ?? $this->Translate->get_translate_module_phrase('module_page_tickets', '_catRemoved');
            $ticket['checkAvatar'] = $this->General->checkAvatar($ticket['steamid']);
        }
        unset($ticket);

        return ['status' => 'success', 'archive' => $tickets, 'admin' => (int)$admin, 'translate' => $translate, 'delete' => (int)$delete, 'count' => count($tickets), 'total' => $total, 'page' => (int)$page];
    }

    public function openTicket($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')];
        }
        $this->ds->openTicket($id);
        if ($this->jr->getCache('noti')['url']) {
            $topic = $this->ds->getTopic($id);
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_ticket') . " №" . $id . " " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_resumed'), "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_byUser') . htmlspecialchars($this->General->checkName($_SESSION['steamid64'])) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)\n# [" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_goToTicket') . "](http:" . $this->General->arr_general['site'] . "tickets/chat/$id/)\n" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_subject') . " **$topic**");
            $this->dr->sendDiscordWebhook($embed);
        }
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketIsOpen')];
    }

    public function deleteTicket($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')];
        }
        $this->ds->deleteTicket($id);
        if ($this->jr->getCache('noti')['url']) {
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_ticket') . " №" . $id . " " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_removed'), "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_byUser') . htmlspecialchars($this->General->checkName($_SESSION['steamid64'])) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)");
            $this->dr->sendDiscordWebhook($embed);
        }
        $this->jr->deleteTicketImages($id);
        $this->jr->deleteTicketJSON($id);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketRemoved')];
    }
}
