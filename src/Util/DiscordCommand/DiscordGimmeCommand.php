<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordGimmeCommand
 *
 * @author lpu8er
 */
class DiscordGimmeCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $hs = [];
        foreach($discordService->getGiveableRolesNames() as $rn) {
            $rns = explode(' ', $rn);
            $hs[] = '`.gimme '.$rns[1].'` give role '.$rn;
        }
        $discordService->talk(implode(PHP_EOL, $hs), $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id']))
        if(1 <= count($this->args)) {
            $roleName = trim(preg_replace('`[^a-zA-Z0-9]`', '', array_shift($this->args)));
            if($discordService->giveRole($roleName, $this->data['guild_id'], $this->data['author']['id'])) {
                $discordService->talk('Role `ping '.$roleName.'` given !', $this->data['channel_id']);
            } else {
                $discordService->talk('Fail to give that role, sorry !', $this->data['channel_id']);
            }
        } else {
            $discordService->talk('Syntax : `.gimme <role>`');
        }
    }
}
