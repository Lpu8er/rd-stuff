<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordLeaderboardCommand
 *
 * @author lpu8er
 */
class DiscordLeaderboardCommand extends DiscordCommand {
    const SIZE = 5;
    
    public function help(Discord $discordService) {
        $discordService->talk('`.leaderboard` display the money leaderboard', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        $returns = [];
        $dsl = $discordService->getEntityManager()->getRepository(\App\Entity\DiscordUser::class)->findAll();
        foreach($dsl as $ds) {
            $returns[$ds->getDiscordName().'#'.$ds->getDiscriminator()] = $ds->getMoney();
        }
        arsort($returns);
        $returns = array_slice($returns, 0, static::SIZE);
        $mx = max(10, max(array_map('strlen', array_keys($returns))));
        $msg = 'Money leaderboard : ';
        foreach($returns as $un => $mn) {
            $msg .= PHP_EOL.'**'.str_pad($un, $mx, ' ').'** '.number_format($mn, 2).' :euro: ';
        }
        $discordService->talk($msg, $this->data['channel_id']);
    }
}
