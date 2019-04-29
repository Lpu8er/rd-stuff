<?php
namespace App\Util\DiscordCommand;

/**
 * Description of DiscordHelpCommand
 *
 * @author lpu8er
 */
class DiscordHelpCommand extends DiscordCommand {
    public function help(\App\Service\Discord $discordService) {
        $discordService->talk('`.help <cmd>` give some help about `<cmd>` command', $this->data['channel_id']);
    }
    
    public function execute(\App\Service\Discord $discordService) {
        if(1 <= count($this->args)) {
            $sub = preg_replace('`[^a-zA-Z0-9]`', '', array_shift($this->args));
            if($discordService->isAllowedCommand($sub)) {
                try {
                    $o = parent::load($sub, [], $this->data);
                    if(!empty($o)) {
                        $o->help($discordService);
                    } else {
                        $discordService->talk('Unimplemented command `'.$sub.'`');
                    }
                } catch (Exception $ex) {
                    var_dump($ex->getMessage());
                    $discordService->talk('An error occured, please retry later', $this->data['channel_id']);
                }
            } else {
                $discordService->talk('Unrecognized command `'.$sub.'`');
            }
        } else {
            // @TODO list commands instead, using discordService
            $discordService->talk('Syntax : `.help <cmd>` give some help about `<cmd>` command', $this->data['channel_id']);
        }
    }
}
