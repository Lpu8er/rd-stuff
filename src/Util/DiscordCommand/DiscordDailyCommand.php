<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;
use DateInterval;
use DateTime;

/**
 * Description of DiscordDailyCommand
 *
 * @author lpu8er
 */
class DiscordDailyCommand extends DiscordCommand {
    const MONEYDINERO = 100; // @TODO
    const STRIKE_NB = 5;
    const STRIKE_DERIV = 20; // % of deriv
    
    public function help(Discord $discordService) {
        $discordService->talk('`.daily` give you some money once a day. *There is a bonus strike if you do it really daily !*', $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id'])) {
            $u = $discordService->findOrCreateUser($this->data['author']['id'], $this->data['author']['username'], $this->data['author']['discriminator']);
            $da = $u->getDailyAsk();
            $di = null;
            if(empty($da) || (0 < $da->diff(new DateTime)->days)) {
                $strk = false;
                $dst = $u->getDailyStrike();
                $bonus = 0;
                if(!empty($da)) {
                    $di = $da->diff(new DateTime);
                    if(2 > $di->days) {
                        $strk = true;
                        $dst++;
                        if(static::STRIKE_NB <= $dst) { // strike complete !
                            $bonus = round(static::MONEYDINERO * (mt_rand(100 - static::STRIKE_DERIV, 100 + static::STRIKE_DERIV) / 100), 2);
                            $u->setDailyStrike(0);
                        } else {
                            $u->setDailyStrike($dst);
                        }
                    }
                }
                $u->setMoney(static::MONEYDINERO + $bonus + $u->getMoney());
                $u->setDailyAsk(new DateTime);
                $discordService->saveUser($u);
                $discordService->enableDelay();
                $discordService->talk('Won '.number_format(static::MONEYDINERO, 2).' :euro:');
                // strike ?
                if($strk) {
                    $msg = '';
                    $strokes = ['b', 'o', 'n', 'u', 's'];
                    for($i = 0; $i < $dst; $i++) {
                        $msg .= ' :regional_indicator_'.$strokes[$i].': ';
                    }
                    if(static::STRIKE_NB <= $dst) {
                        $msg .= ' Strike complete ! You won a '.number_format($bonus, 2).' :euro: bonus !';
                    }
                    $discordService->talk($msg);
                }
                static::load('money', $this->args, $this->data)->execute($discordService);
                $discordService->flush($this->data['channel_id']);
                $discordService->disableDelay();
            } else {
                $sms = [];
                $di = $da->diff((new DateTime)->sub(new DateInterval('P1D')));
                if($di->h) { $sms[] = ''.$di->h.' hour'.((1 < $di->h)? 's':''); }
                if($di->i) { $sms[] = ''.$di->i.' minute'.((1 < $di->i)? 's':''); }
                if($di->s) { $sms[] = ''.$di->s.' second'.((1 < $di->s)? 's':''); }
                $discordService->talk('A daily once a day will grant stuff. More, it won\'t. Please wait something like '.implode(', ', $sms).' .', $this->data['channel_id']);
            }
        }
    }
}
