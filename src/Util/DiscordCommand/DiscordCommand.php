<?php
namespace App\Util\DiscordCommand;

use App\Service\Discord;

/**
 * Description of DiscordCommand
 *
 * @author lpu8er
 */
abstract class DiscordCommand {
    /**
     * 
     * @param string $name
     * @param array $args
     * @param array $data
     * @return DiscordCommand
     */
    final public static function load(string $name, array $args, array $data): ?DiscordCommand {
        $returns = null;
        $cls = __NAMESPACE__.'\\Discord'.ucfirst($name).'Command';
        if(class_exists($cls)) {
            $cmdObj = new $cls($name, $args, $data);
            if(!empty($cmdObj) && is_a($cmdObj, __CLASS__)) {
                $returns = $cmdObj;
            }
        }
        return $returns;
    }
    
    protected $name;
    protected $args;
    protected $data;
    
    protected function __construct(string $name, array $args, array $data) {
        $this->name = $name;
        $this->args = $args;
        $this->data = $data;
    }
    
    final public function getName() {
        return $this->name;
    }
    
    abstract public function help(Discord $discordService);
    abstract public function execute(Discord $discordService);
}
