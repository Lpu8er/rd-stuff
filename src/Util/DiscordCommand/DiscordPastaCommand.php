<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordPastaCommand
 *
 * @author lpu8er
 */
class DiscordPastaCommand extends DiscordCommand {
    public function help(Discord $discordService) {
        $discordService->enableDelay();
        $discordService->talk('`.pasta` give the pasta list', $this->data['channel_id']);
        $discordService->talk('`.pasta <pasta>` print the asked pasta', $this->data['channel_id']);
        $discordService->flush($this->data['channel_id']);
        $discordService->disableDelay();
    }
    
    protected function getList() {
        $pastas = [];
        $pastas['popipo'] = <<<'EOT'
https://www.youtube.com/watch?v=mco3UX9SqDA
```https://www.youtube.com/watch?v=mco3UX9SqDA```
EOT;
        $pastas['despapipo'] = <<<'EOT'
https://www.youtube.com/watch?v=1P1c-ML9MMI
```https://www.youtube.com/watch?v=1P1c-ML9MMI```
EOT;
        
        $pastas['navyseal'] = <<<'EOT'
```What the fuck did you just fucking say about me, you little bitch?
I'll have you know I graduated top of my class in the Navy Seals,
and I've been involved in numerous secret raids on Al-Quaeda,
and I have over 300 confirmed kills.
I am trained in gorilla warfare
and I'm the top sniper in the entire US armed forces.
You are nothing to me but just another target.
I will wipe you the fuck out with precision the likes of which has never been seen before on this Earth,
mark my fucking words.
You think you can get away with saying that shit to me over the Internet?
Think again, fucker. As we speak I am contacting my secret network of spies across the USA
and your IP is being traced right now so you better prepare for the storm, maggot.
The storm that wipes out the pathetic little thing you call your life.
You're fucking dead, kid. I can be anywhere, anytime,
and I can kill you in over seven hundred ways,
and that's just with my bare hands.
Not only am I extensively trained in unarmed combat,
but I have access to the entire arsenal of the United States Marine Corps
and I will use it to its full extent to wipe your miserable ass off the face of the continent,
you little shit. If only you could have known what unholy retribution
your little “clever” comment was about to bring down upon you,
maybe you would have held your fucking tongue.
But you couldn't, you didn't, and now you're paying the price,
you goddamn idiot. I will shit fury all over you and you will drown in it.
You're fucking dead, kiddo.```
EOT;
        
        // "aliases"
        $pastas['po'] = $pastas['popipo'];
        $pastas['despacito'] = $pastas['despapipo'];
        $pastas['navy'] = $pastas['navyseal'];
        
        return $pastas;
    }
    
    protected function getDescription() {
        return [
            'popipo' => 'PO PI PO',
            'despapipo' => 'Nobody wants this anyway.',
            'navyseal' => 'The famous one.',
            
            'po' => 'Alias of `popipo`',
            'despacito' => 'Alias of `despapipo`',
            'navy' => 'Alias of `navyseal`',
        ];
    }
    
    public function execute(Discord $discordService) {
        
        
        if(1 <= count($this->args)) {
            $fc = trim(preg_replace('`[^a-zA-Z0-9]`', '', array_shift($this->args)));
            $pastas = $this->getList();
            if(array_key_exists($fc, $pastas)) {
                $discordService->talk($pastas[$fc], $this->data['channel_id']);
            } else {
                $discordService->talk('Unknwn pasta : `'.$fc.'`', $this->data['channel_id']);
            }
        } else {
            $discordService->talk('Available pasta : `'.implode('`, `', array_keys($pastas)).'`', $this->data['channel_id']);
        }
    }
}
