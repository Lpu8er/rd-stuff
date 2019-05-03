<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordPongCommand
 *
 * @author lpu8er
 */
class DiscordPongCommand extends DiscordAdmin {
    public function execute(Discord $discordService) {
        $discordService->talk('SPOOKY SCARY SKELETONS');
    }
}
