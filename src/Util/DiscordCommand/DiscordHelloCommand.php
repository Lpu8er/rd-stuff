<?php
namespace App\Util\DiscordCommand;

/**
 * Description of DiscordHelloCommand
 *
 * @author lpu8er
 */
class DiscordHelloCommand extends DiscordCommand {
    public function help(\App\Service\Discord $discordService) {
        throw new BadMethodCallException('Unimplemented');
    }
    
    public function execute(\App\Service\Discord $discordService) {
        $discordService->talk('Hello World', $this->data['channel_id']);
    }
}
