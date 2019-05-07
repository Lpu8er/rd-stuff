<?php
namespace App\Util\DiscordCommand;

use App\Entity\Hangman;
use App\Service\Discord;

/**
 * Description of DiscordHangmanCommand
 *
 * @author lpu8er
 */
class DiscordHangmanCommand extends DiscordCommand {
    const MAXTRIES = 10; // @TODO
    
    public function help(Discord $discordService) {
        $msg = [
            '`.hangman` starts an hangman session if none is currently going.',
            '`.hangman <letter>` try for the `<letter>` on the current hangman session.',
            'Maximum errors for a session : '.static::MAXTRIES,
        ];
        $discordService->talk(implode(PHP_EOL, $msg), $this->data['channel_id']);
    }
    
    public function execute(Discord $discordService) {
        $hrep = $discordService->getEntityManager()->getRepository(Hangman::class);
        if(1 <= count($this->args)) {
            $h = $hrep->getCurrent();
            if(!empty($h)) {
                $letter = substr(strtoupper(preg_replace('`[^a-zA-Z]`', '', trim(array_shift($this->args)))), 0, 1);
                if(empty($letter)) {
                    $discordService->talk('Dude. Send just a normal letter.', $this->data['channel_id']);
                } else {
                    $cres = $hrep->testLetter($h, $letter);
                    $msg = [];
                    if(Hangman::LETTER_FOUND & $cres) {
                        $msg[] = ':white_check_mark: **letter found** !';
                        
                    } elseif(Hangman::LETTER_ALREADY_TRIED & $cres) {
                        $msg[] = ':interrobang: letter already tried !';
                    } else {
                        $msg[] = ':red_circle: not found !';
                    }
                    $msg[] = $h->getFunDiscovered();
                    if(Hangman::SUCCESS & $cres) {
                        $msg[] = ':military_medal: **Success !**';
                        $hrep->blast($h);
                    } elseif($h->getTries() >= static::MAXTRIES) {
                        $msg[] = ':japanese_ogre: **FAILED** :japanese_ogre: '.$h->getFunWholeWord().' :japanese_ogre: ';
                        $hrep->blast($h);
                    } else {
                        $msg[] = '*Testé ('.$h->getTries().'/'.static::MAXTRIES.') : '.$h->getLetters().'*';
                    }
                    $discordService->talk(implode(PHP_EOL, $msg), $this->data['channel_id']);
                }
            } else {
                $discordService->talk('No hangman session started', $this->data['channel_id']);
            }
        } else {
            $h = $hrep->getCurrent();
            if(empty($h)) {
                $h = $hrep->generate();
                $msg = [];
                $msg[] = 'NEW HANGMAN SESSION STARTED !';
                $msg[] = $h->getFunDiscovered();
                $discordService->talk(implode(PHP_EOL, $msg), $this->data['channel_id']);
            } else {
                $discordService->talk('An hangman session is already started', $this->data['channel_id']);
            }
        }
    }
}
