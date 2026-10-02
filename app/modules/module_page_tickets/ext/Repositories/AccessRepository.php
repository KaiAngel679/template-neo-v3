<?php

namespace app\modules\module_page_tickets\ext\Repositories;

class AccessRepository
{
    public function getCategoryNameFromId($stringId, $categories)
    {
        $ids = array_map('trim', explode(';', $stringId));
        $map = [];
        foreach ($categories as $cat) {
            $map[$cat['id']] = $cat['title'];
        }
        $titles = [];
        foreach ($ids as $id) {
            if (isset($map[$id])) {
                $titles[] = $map[$id];
            }
        }
        return implode(';', $titles);
    }
}
