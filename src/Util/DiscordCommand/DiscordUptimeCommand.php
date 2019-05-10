<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;
use DateInterval;
use DateTime;

/**
 * Description of DiscordUptimeCommand
 *
 * @author lpu8er
 */
class DiscordUptimeCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $discordService->talk('`.uptime` tells the uptime of both the event loop and connection', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        $sd = $discordService->getStartDate();
        $cd = $discordService->getConnectionDate();
        $msg = [];
        $msg[] = 'Loop uptime : '.$this->getTimeSpan($sd);
        $msg[] = 'Connection uptime : '.(empty($cd)? 'DED':$this->getTimeSpan($cd));
        $discordService->talk(implode(PHP_EOL, $msg), $this->data['channel_id']);
    }
    
    /**
     * 
     * @param DateTime $st
     * @return string
     */
    protected function getTimeSpan(DateTime $st): string {
        $sms = [];
        $di = $st->diff(new DateTime);
        if($di->d) { $sms[] = ''.$di->d.' day'.((1 < $di->d)? 's':''); }
        if($di->h) { $sms[] = ''.$di->h.' hour'.((1 < $di->h)? 's':''); }
        if($di->i) { $sms[] = ''.$di->i.' minute'.((1 < $di->i)? 's':''); }
        if($di->s) { $sms[] = ''.$di->s.' second'.((1 < $di->s)? 's':''); }
        return implode(', ', $sms);
    }
}
