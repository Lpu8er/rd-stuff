<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;
use DateTime;

/**
 * Description of DiscordDailyCommand
 *
 * @author lpu8er
 */
class DiscordDailyCommand extends DiscordCommand {
    const MONEYDINERO = 100; // @TODO
    
    public function help(Discord $discordService) {
        $discordService->talk('`.daily` give you some money once a day', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id'])) {
            $u = $discordService->findOrCreateUser($this->data['author']['id'], $this->data['author']['username'], $this->data['author']['discriminator']);
            $da = $u->getDailyAsk();
            if(empty($da) || (0 < $da->diff(new DateTime)->days)) {
                $u->setMoney(static::MONEYDINERO + $u->getMoney());
                $u->setDailyAsk(new DateTime);
                $discordService->saveUser($u);
                $discordService->enableDelay();
                $discordService->talk('Won '.number_format(static::MONEYDINERO, 2).' :euro:');
                static::load('money', $this->args, $this->data)->execute($discordService);
                $discordService->flush($this->data['channel_id']);
                $discordService->disableDelay();
            } else {
                $discordService->talk('A daily once a day will grant stuff. More, it won\'t. Last time used : '.($da->format('d/m/Y H:i:s')), $this->data['channel_id']);
            }
        }
    }
}
