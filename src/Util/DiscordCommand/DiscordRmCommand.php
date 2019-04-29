<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordRmCommand
 *
 * @author lpu8er
 */
class DiscordRmCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $hs = [];
        foreach($discordService->getGiveableRolesNames() as $rn) {
            $rns = explode(' ', $rn);
            $hs[] = '`.rm '.$rns[1].'` remove role '.$rn;
        }
        $discordService->talk(implode(PHP_EOL, $hs), $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id']))
        if(1 <= count($this->args)) {
            $roleName = trim(preg_replace('`[^a-zA-Z0-9]`', '', array_shift($this->args)));
            if($discordService->removeRole($roleName, $this->data['guild_id'], $this->data['author']['id'])) {
                $discordService->talk('Role `ping '.$roleName.'` removed !', $this->data['channel_id']);
            } else {
                $discordService->talk('Fail to remove that role, sorry !', $this->data['channel_id']);
            }
        } else {
            $discordService->talk('Syntax : `.rm <role>`');
        }
    }
}
