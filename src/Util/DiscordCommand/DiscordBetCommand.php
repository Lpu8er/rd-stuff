<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordBetCommand
 *
 * @author lpu8er
 */
class DiscordBetCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $msg = '`.bet <amount> <bet>` place a bet of `<amount>` :euro: to have the `<bet>` result of a 100-face dice';
        $msg.= PHP_EOL;
        $msg.= 'Exact match :champagne: : `<amount> * 3` :euro:';
        $msg.= PHP_EOL;
        $msg.= 'Near match (+/-5) :second_place: : `<amount> * 2` :euro:';
        $msg.= PHP_EOL;
        $msg.= 'Lucky strike (one number) :shield: : `<amount> * 1` :euro:';
        $discordService->talk($msg, $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id'])) {
            if(2 <= count($this->args)) {
                $amount = intval(preg_replace('`[^0-9]`', '', array_shift($this->args)));
                $bet = intval(preg_replace('`[^0-9]`', '', array_shift($this->args)));
                if(!empty($amount) && !empty($bet) && ($bet <= 100)) {
                    $u = $discordService->findOrCreateUser($this->data['author']['id'], $this->data['author']['username'], $this->data['author']['discriminator']);
                    if($amount <= $u->getMoney()) {
                        $nam = ($u->getMoney() - $amount);
                        $rnd = mt_rand(1, 100);
                        $dzn = floor($rnd / 10); $unt = $rnd - (10 * $dzn);
                        $bzn = floor($bet / 10); $bnt = $bet - (10 * $bzn);
                        $msg = ':game_die: **'.strval($rnd).'** ';
                        if($rnd == $bet) {
                            $nam+= ($amount * 3);
                            $msg.= ':champagne: You **won** '.number_format($amount * 3, 2).' :euro: !';
                        } elseif(($rnd >= ($bet - 5)) && ($rnd <= ($bet + 5))) {
                            $nam+= ($amount * 2);
                            $msg.= ':second_place: You **won** '.number_format($amount * 2, 2).' :euro: !';
                        } elseif(($dzn == $bzn) || ($unt == $bnt)) {
                            $nam+= $amount;
                            $msg.= ':shield: You **won** '.number_format($amount, 2).' :euro: !';
                        } else {
                            $msg.= ':skull: **RIP**';
                        }
                        $u->setMoney($nam);
                        $discordService->saveUser($u);
                        $msg .= PHP_EOL.'You now have '.number_format($nam, 2).' :euro: !';
                        $discordService->talk($msg, $this->data['channel_id']);
                    } else {
                        $discordService->talk('You cannot bet what you do not have. Meaning you have only **'.$u->getMoney().'** :euro:', $this->data['channel_id']);
                    }
                } else {
                    $this->help($discordService);
                }
            } else {
                $this->help($discordService);
            }
        }
    }
}
