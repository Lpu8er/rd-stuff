<?php
namespace App\Util\DiscordCommand;

use App\Entity\DiscordUser;
use App\Entity\Hangman;
use App\Entity\HangmanUser;
use App\Service\Discord;
use Exception;

/**
 * Description of DiscordHangmanCommand
 *
 * @author lpu8er
 */
class DiscordHangmanCommand extends DiscordCommand {
    const REWARD = 100;
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
        if(!empty($this->data['author'])
                && !empty($this->data['author']['id'])
                && empty($this->data['webhook_id'])) {
            $u = $discordService->findOrCreateUser($this->data['author']['id'], $this->data['author']['username'], $this->data['author']['discriminator']);
            $hrep = $discordService->getEntityManager()->getRepository(Hangman::class);
            if(1 <= count($this->args)) {
                $h = $hrep->getCurrent();
                if(!empty($h)) {
                    $letter = substr(strtoupper(preg_replace('`[^a-zA-Z]`', '', trim(array_shift($this->args)))), 0, 1);
                    if(empty($letter)) {
                        $discordService->talk('Dude. Send just a normal letter.', $this->data['channel_id']);
                    } else {
                        $this->appendUser($discordService, $h, $u);
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
                            // hop là, give money
                            $parts = $hrep->findBy(['hangman_id' => $h->getId(),]);
                            $partsNames = []; $indReward = floor(static::REWARD / count($parts));
                            foreach ($parts as $part) {
                                $pu = $part->getUser();
                                $partsNames[] = $pu->getName();
                                $pu->setMoney($pu->getMoney() + $indReward);
                                $discordService->saveUser($pu);
                            }
                            $msg[] = ':military_medal: **Success !** '.implode(', ', $partsNames).' received '.number_format($indReward, 2).' :euro:';
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
    
    /**
     * 
     * @param Discord $discordService
     * @param Hangman $h
     * @param DiscordUser $u
     */
    protected function appendUser(Discord $discordService, Hangman $h, DiscordUser $u) {
        $hur = $discordService->getEntityManager()->getRepository(HangmanUser::class);
        $hu = $hur->find(['hangman' => $h->getId(), 'user' => $u->getId(),]);
        if(empty($hu)) {
            $hu = new HangmanUser($h, $u);
            $discordService->getEntityManager()->persist($hu);
            $discordService->getEntityManager()->flush();
        }
    }
}
