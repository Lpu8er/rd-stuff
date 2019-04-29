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
    
    protected function loadHelp($cmd, \App\Service\Discord $discordService) {
        $o = parent::load($cmd, [], $this->data);
        if(!empty($o)) {
            $o->help($discordService);
        } else {
            $discordService->talk('Unimplemented command `'.$cmd.'`');
        }
    }
    
    public function execute(\App\Service\Discord $discordService) {
        if(1 <= count($this->args)) {
            $sub = preg_replace('`[^a-zA-Z0-9]`', '', array_shift($this->args));
            if($discordService->isAllowedCommand($sub)) {
                $sub = $discordService->getAliasedCommand($sub);
                try {
                    $this->loadHelp($sub, $discordService);
                } catch (Exception $ex) {
                    var_dump($ex->getMessage());
                    $discordService->talk('An error occured, please retry later', $this->data['channel_id']);
                }
            } else {
                $discordService->talk('Unrecognized command `'.$sub.'`');
            }
        } else {
            $discordService->enableDelay();
            foreach($discordService->getAllowedCommands() as $c) {
                $as = $discordService->getAliasedCommand($c);
                if($c == $as) {
                    $discordService->talk('**'.$c.'**');
                    $this->loadHelp($c, $discordService);
                } else {
                    $discordService->talk('**'.$c.'** : Alias of `'.$as.'`', $this->data['channel_id']);
                }
            }
            $discordService->flush($this->data['channel_id']);
        }
    }
}
