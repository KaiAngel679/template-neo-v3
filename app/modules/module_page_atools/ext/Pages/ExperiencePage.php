<?php

namespace app\modules\module_page_atools\ext\Pages;

use app\modules\module_page_atools\ext\Controllers\AccessController;
use app\modules\module_page_atools\ext\ModuleContainer;

final class ExperiencePage extends AbstractPage
{
    public function isAccessible(AccessController $access, string $section): bool
    {
        return $access->checkPermission('experience.view');
    }

    protected function actions(): array
    {
        return [
            'get_experience_list' => [
                'handler' => static fn(ModuleContainer $c) => $c->experience()->getExperienceList(
                    (array) ($_POST['servers'] ?? []),
                    (string) ($_POST['sort'] ?? 'down'),
                    (int) ($_POST['limit'] ?? 10),
                    (int) ($_POST['offset'] ?? 0),
                    (string) ($_POST['search'] ?? ''),
                    (string) $_SESSION['steamid'],
                ),
            ],
            'add_experience' => [
                'permission' => 'experience.update',
                'handler' => static fn(ModuleContainer $c) => $c->experience()->addExperience(
                    (string) ($_POST['steamid'] ?? ''),
                    $_POST['amount'] ?? '',
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
            'update_experience' => [
                'permission' => 'experience.update',
                'handler' => static fn(ModuleContainer $c) => $c->experience()->updateExperience(
                    (string) ($_POST['stats_key'] ?? ''),
                    (string) ($_POST['steam'] ?? ''),
                    $_POST['value'] ?? '',
                    $_POST['old_value'] ?? '',
                ),
            ],
            'reset_experiences' => [
                'permission' => 'experience.reset',
                'handler' => static fn(ModuleContainer $c) => $c->experience()->resetExperiences(
                    (array) ($_POST['player_list'] ?? []),
                ),
            ],
            'wipe_experience_stats' => [
                'permission' => 'experience.reset',
                'handler' => static fn(ModuleContainer $c) => $c->experience()->wipeStats(
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
            'delete_empty_experience_players' => [
                'permission' => 'experience.reset',
                'handler' => static fn(ModuleContainer $c) => $c->experience()->deleteEmptyPlayers(
                    (array) ($_POST['servers'] ?? []),
                ),
            ],
        ];
    }

    public function context(ModuleContainer $container, string $section): array
    {
        return [
            'servers' => $container->servers()->getServers(),
            'defaultAllServers' => $container->defaultAllServers(),
        ];
    }
}
