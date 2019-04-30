<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordMoneyCommand
 *
 * @author lpu8er
 */
class DiscordMoneyCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $discordService->talk('`.money` display your money', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id'])) {
            $u = $discordService->findOrCreateUser($this->data['author']['id'], $this->data['author']['username'], $this->data['author']['discriminator']);
            $discordService->talk('You have currently '.number_format($u->getMoney(), 2).' :euro:');
        }
    }
}
