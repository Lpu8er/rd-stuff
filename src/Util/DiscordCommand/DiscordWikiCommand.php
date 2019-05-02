<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordWikiCommand
 *
 * @author lpu8er
 */
class DiscordWikiCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $discordService->talk('`.wiki <page>` generate a wikipedia link to page `<page>` (that may or may not exists)', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(1 <= count($this->args)) {
            $fc = trim(preg_replace('`\W`', '', array_shift($this->args)));
            $ln = 'https://fr.wikipedia.org/wiki/'.urlencode($fc);
            $discordService->embed('Wikipédia - '.$fc, $ln, [], 'e0e0e0', $this->data['channel_id']);
        }
    }
}
