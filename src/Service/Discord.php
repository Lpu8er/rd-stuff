<?php
namespace App\Service;

use App\Util\REST;
use Exception;
use Ratchet\Client\Connector as ClientConnector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Factory;
use React\Socket\Connector as ReactConnector;

/**
 * Description of Discord
 *
 * @author lpu8er
 */
class Discord {
    const OP_MESSAGE = 0;
    const OP_HEARTBEAT = 1;
    const OP_IDENTIFY = 2;
    const OP_HANDSHAKE = 10;
    
    const EVENT_HEARTBEAT = 'HEARTBEAT';
    const EVENT_IDENTIFY = 'IDENTIFY';
    const EVENT_READY = 'READY';
    const EVENT_RESUME = 'RESUME';
    const EVENT_RESUMED = 'RESUMED';
    const EVENT_GUILD_CREATE = 'GUILD_CREATE'; // happens at first connections, may help to lay load stuff !
    const EVENT_TYPING_START = 'TYPING_START';
    const EVENT_MESSAGE_CREATE = 'MESSAGE_CREATE';
    const EVENT_PRESENCE_UPDATE = 'PRESENCE_UPDATE';
    
    /**
     *
     * @var string
     */
    protected $uri;
    /**
     *
     * @var string
     */
    protected $scope;
    /**
     *
     * @var string
     */
    protected $token;
    /**
     *
     * @var string
     */
    protected $channel = null;
    
    /**
     *
     * @var array
     */
    protected $guilds = null;
    
    /**
     *
     * @var string
     */
    protected $gatewayUri = null;
    
    /**
     *
     * @var array
     */
    protected $giveableRoles = [];
    
    /**
     *
     * @var array
     */
    protected $allowedCommands = [];
    
    /**
     *
     * @var array 
     */
    protected $rolesCache = [];
    
    /**
     *
     * @var \React\EventLoop\LoopInterface
     */
    protected $loop = null;
    
    protected $reactConnector = null;
    
    /**
     *
     * @var WebSocket
     */
    protected $ws = null;
    
    protected $lastSequence = null;
    
    protected $hbInterval = null;
    
    protected $sessionId = null;
    
    /**
     * 
     * @param string $uri
     * @param string $token
     * @param string $scope
     */
    public function __construct($uri, $token, $scope, $channel, $giveableRoles, $allowedCommands) {
        $this->uri = $uri;
        $this->token = $token;
        $this->scope = $scope;
        $this->channel = $channel;
        $this->giveableRoles = $giveableRoles;
        $this->allowedCommands = $allowedCommands;
    }
    
    /**
     * 
     */
    public function getGuilds() {
        $this->guilds = [];
        $response = REST::json($this->uri, '/users/@me/guilds', null, [], [
            'Authorization' => 'Bot '.$this->token,
        ]);
        if($response->isValid()) {
            foreach($response->getContent() as $guild) {
                // grab emojis
                $sres = REST::json($this->uri, '/guilds/'.$guild['id'].'/emojis', null, [], [
                    'Authorization' => 'Bot '.$this->token,
                ]);
                $guild['emojis'] = $sres;
                $this->guilds[$guild['id']] = $guild;
            }
        }
    }
    
    /**
     * 
     */
    public function getGatewayUri() {
        $response = REST::json($this->uri, '/gateway/bot', null, [], [
            'Authorization' => 'Bot '.$this->token,
        ]);
        if($response->isValid()) {
            $d = $response->getContent();
            $this->gatewayUri = $d['url'];
        }
    }
    
    /**
     * 
     */
    public function connect() {
        $this->getGuilds();
        $this->getGatewayUri();
        
        $this->loop = Factory::create();
        $this->reactConnector = new ReactConnector($this->loop, [
            'dns' => '8.8.8.8',
            'timeout' => 10
        ]);
        $connector = new ClientConnector($this->loop, $this->reactConnector);
        $connector($this->gatewayUri)->then([$this, 'onConnect'], [$this, 'onConnectError']);
        $this->loop->run();
    }
    
    /**
     * 
     */
    public function heartbeat() {
        $this->ws->send(json_encode(['op' => static::OP_HEARTBEAT, 'd' => $this->lastSequence]));
        $this->loop->addTimer($this->hbInterval, [$this, 'heartbeat']);
    }
    
    /**
     * 
     * @param mixed $msg
     * @param int $op
     * @param string $e
     * @param int $s
     */
    public function send($msg, $op, $e, $s = 0) {
        $this->ws->send(json_encode([
            'op' => $op,
            'd' => $msg,
            's' => $s,
            't' => $e,
        ]));
    }
    
    /**
     * 
     * @param WebSocket $conn
     */
    public function onConnect(WebSocket $conn) {
        $this->ws = $conn;
        $this->ws->on('message', [$this, 'onMessage']);
        $this->ws->on('close', [$this, 'onClose']);
    }
    
    /**
     * 
     * @param type $code
     * @param type $reason
     */
    public function onClose($code = null, $reason = null) {
        echo "Connection closed ({$code} - {$reason})\n";
    }
    
    /**
     * 
     * @param MessageInterface $msg
     */
    public function onMessage(MessageInterface $msg) {
        $this->parseOperation($msg);
        /*$this->ws->close();*/
    }
    
    /**
     * 
     * @param Exception $e
     */
    public function onConnectError(Exception $e) {
        echo "Could not connect: {$e->getMessage()}\n";
        $this->loop->stop();
    }
    
    /**
     * 
     * @param string $literal
     */
    protected function parseOperation($literal) {
        $js = json_decode($literal, true);
        $op = intval($js['op']);
        if(!empty($js['s'])) {
            $this->lastSequence = intval($js['s']);
        }
        switch($op) {
            case static::OP_HANDSHAKE:
                if(!empty($js['d']['heartbeat_interval'])) {
                    $this->hbInterval = max(1, floor(intval($js['d']['heartbeat_interval']) / 1000));
                    // start heartbeating
                    $this->loop->addTimer($this->hbInterval, [$this, 'heartbeat']);
                    // identify
                    $this->send([
                        'token' => $this->token,
                        'properties' => [
                            '$os' => 'linux',
                            '$browser' => 'cli',
                            '$device' => 'php',
                        ],
                    ], static::OP_IDENTIFY, static::EVENT_IDENTIFY);
                }
                break;
            case static::OP_MESSAGE:
                $this->parseEvent($js['t'], $js['d']);
                break;
        }
    }
    
    /**
     * 
     * @param string $event
     * @param array $data
     */
    protected function parseEvent(string $event, $data) {
        if(static::EVENT_READY === $event) {
            $this->sessionId = $data['session_id']; // we shall keep it in a file or whatever
        } elseif(static::EVENT_GUILD_CREATE === $event) {
            foreach($data['roles'] as $role) {
                if(in_array($role['name'], $this->giveableRoles)) {
                    $this->rolesCache[$role['id']] = $role['name'];
                }
            }
        } elseif(static::EVENT_MESSAGE_CREATE === $event) {
            $this->parseMessage($data);
        } else {
            var_dump($event);
        }
    }
    
    /**
     * 
     * @param array $data
     */
    protected function parseMessage($data) {
        // check if that's a bot message
        $matches = [];
        if(!$data['tts']
                && (empty($this->channel) || ($data['channel_id'] === $this->channel))
                && preg_match('`^\.([a-zA-Z0-9-]+)(( +)(.+))?$`', $data['content'], $matches)) { // @TODO better includes of "." as joker
            $this->parseCommand($matches[1], empty($matches[4])? []:explode(' ', $matches[4]), $data);
        }
    }
    
    /**
     * 
     * @param string $cmd
     * @param array $args
     * @param array $pureData
     */
    protected function parseCommand(string $cmd, array $args, array $pureData) {
        if(in_array($cmd, $this->allowedCommands)) {
            $mn = 'doCmd'.ucfirst($cmd);
            if(method_exists($this, $mn)) {
                $this->$mn($args, $pureData);
            } else {
                $this->talk('Unimplemented command "'.$cmd.'"');
            }
        } else {
            $this->talk('Unrecognized command "'.$cmd.'"');
        }
    }
    
    protected function doCmdHello(array $args) {
        $this->talk('Hello world');
    }
    
    protected function doCmdHelp(array $args) {
        $this->talk('Help yourself, for now.');
    }
    
    /**
     * 
     * @param mixed $msg
     * @param ?string $channel
     */
    protected function talk($msg, $channel = null) {
        if(empty($channel)) { $channel = $this->channel; }
        $response = REST::json($this->uri, '/channels/'.$channel.'/messages', REST::METHOD_POST, [
            'content' => $msg,
        ], [
            'Authorization' => 'Bot '.$this->token,
        ]);
    }
    
    /**
     * 
     */
    public function resume() {
        $this->send([
            'token' => $this->token,
            'session_id' => $this->sessionId,
            'seq' => $this->lastSequence,
            ], static::OP_MESSAGE, static::EVENT_RESUME);
    }
    
    /**
     * 
     * @param string $name
     * @param bool $ignoreCache
     * @return ?string
     */
    protected function getRoleId(string $name, bool $ignoreCache = false) {
        $returns = null;
        if(!$ignoreCache && in_array($name, $this->rolesCache)) {
            $returns = array_search($name, $this->rolesCache);
        } else {
            // @TODO
        }
        return $returns;
    }
    
    /**
     * 
     * @param string $trg
     * @return string|null
     */
    protected function isGiveable(string $trg): ?string {
        $returns = null;
        $rolename = 'ping '.preg_replace('`[^a-z0-9]`', '', $trg);
        if(in_array($rolename, $this->giveableRoles)) {
            $rid = $this->getRoleId($rolename);
            if(!empty($rid)) {
                $returns = $rid;
            }
        }
        return $returns;
    }
    
    public function giveRole() {
        $rid = $this->isGiveable($trg);
        if(!empty($rid)) {
            $response = REST::json($this->uri, '/channels/'.$channel.'/messages', REST::METHOD_POST, [
                'content' => $msg,
            ], [
                'Authorization' => 'Bot '.$this->token,
            ]);
        }
    }
}
