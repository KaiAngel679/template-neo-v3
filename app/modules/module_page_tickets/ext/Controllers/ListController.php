<?php

namespace app\modules\module_page_tickets\ext\Controllers;

use app\modules\module_page_tickets\ext\Services\DatabaseService;
use app\modules\module_page_tickets\ext\Repositories\ChatRepository;
use app\modules\module_page_tickets\ext\Repositories\JsonRepository;
use app\modules\module_page_tickets\ext\Repositories\DiscordRepository;
use app\modules\module_page_tickets\ext\Services\BalanceService;

class ListController
{
    protected $ds, $cr, $jr, $dr, $bs, $Translate, $General;

    public function __construct($Db, $Translate, $General)
    {
        $this->ds = new DatabaseService($Db);
        $this->cr = new ChatRepository;
        $this->jr = new JsonRepository;
        $this->dr = new DiscordRepository($General);
        $this->bs = new BalanceService($Db, $Translate);
        $this->Translate = $Translate;
        $this->General = $General;

        if ($this->jr->getCache('settings')['auto_close']) {
            foreach ($this->ds->getTickets() as $key) {
                if (time() - $key['edit_at'] > $this->jr->getCache('settings')['duration']) {
                    $this->ds->closeTicketAuto($key['id']);
                }
            }
        }
    }

    public function constructList($category, $server, $limit, $search, $my, $page)
    {
        if (!is_numeric($limit) || !is_numeric($page)) {
            return ['status' => 'error'];
        }

        $offset = ($page - 1) * $limit;
        $access = $this->ds->getAccessFullCheck($_SESSION['steamid64']);
        $admin = !empty($access['access']);
        $delete = !empty($access['add_delete']) ? 1 : 0;

        if ($admin) {
            $query = $this->ds->getTicketListAdmin($category, $server, $my ? 1000000 : $limit, $search, $my ? 0 : $offset);
        } else {
            $query = $this->ds->getTicketList($_SESSION['steamid64'], $my ? 1000000 : $limit, $my ? 0 : $offset);
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

        return ['status' => 'success', 'list' => $tickets, 'admin' => (int)$admin, 'delete' => (int)$delete, 'count' => count($tickets), 'total' => $total, 'page' => (int)$page];
    }

    public function closeTicket($id)
    {
        if (!is_numeric($id)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketNotNumber')];
        }
        if ($this->ds->getAccessFullCheck($_SESSION['steamid64'])['access']) {
            $summ = $this->ds->getCategoryFull($this->ds->getInfoChat($id)['category_id'])['amount_money'];
            $summ ? $this->bs->addBalance($_SESSION['steamid32'], $summ) : null;
        }
        $this->ds->closeTicket($id, $_SESSION['steamid64']);
        if ($this->jr->getCache('noti')['url']) {
            $topic = $this->ds->getTopic($id);
            $embed = $this->dr->buildEmbed($this->Translate->get_translate_module_phrase('module_page_tickets', '_ticket') . " №" . $id . " " . mb_strtolower($this->Translate->get_translate_module_phrase('module_page_tickets', '_closed')), "-# " . $this->Translate->get_translate_module_phrase('module_page_tickets', '_byUser') . htmlspecialchars($this->General->checkName($_SESSION['steamid64'])) . "**](http:" . $this->General->arr_general['site'] . "profiles/" . $_SESSION['steamid64'] . "/?search=1)\n# [" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_goToTicket') . "](http:" . $this->General->arr_general['site'] . "tickets/chat/$id/)\n" . $this->Translate->get_translate_module_phrase('module_page_tickets', '_subject') . " **$topic**");
            $this->dr->sendDiscordWebhook($embed);
        }
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketIsСlosed')];
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

    public function getCategory()
    {
        return ['status' => 'success', 'categories' => $this->ds->getCategoriesTabs()];
    }

    public function moveTicket($id, $category)
    {
        if (!is_numeric($id) && !is_numeric($category)) {
            return ['status' => 'error', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketOrCatNotNumber')];
        }
        $this->ds->moveTicket($id, $category);
        return ['status' => 'success', 'text' => $this->Translate->get_translate_module_phrase('module_page_tickets', '_ticketMoved')];
    }
}
