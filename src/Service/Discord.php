<?php
namespace App\Service;

use App\Entity\DiscordUser;
use App\Util\DiscordCommand as DiscordCommands;
use App\Util\REST;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Ratchet\Client\Connector as ClientConnector;
use Ratchet\Client\WebSocket;
use Ratchet\RFC6455\Messaging\MessageInterface;
use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use React\Socket\Connector as ReactConnector;
use Symfony\Component\HttpFoundation\Response;

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
    protected $aliases = [];
    
    /**
     *
     * @var array 
     */
    protected $rolesCache = [];
    
    /**
     *
     * @var LoopInterface
     */
    protected $loop = null;
    
    protected $reactConnector = null;
    
    /**
     *
     * @var WebSocket
     */
    protected $ws = null;
    
    /**
     *
     * @var int
     */
    protected $lastSequence = null;
    
    /**
     *
     * @var int
     */
    protected $hbInterval = null;
    
    /**
     *
     * @var int
     */
    protected $sessionId = null;
    
    /**
     *
     * @var array
     */
    protected $flushableTalks = [];
    
    /**
     *
     * @var bool
     */
    protected $delayEnabled = false;
    
    /**
     *
     * @var EntityManagerInterface 
     */
    protected $em = null;
    
    /**
     *
     * @var \Psr\Log\LoggerInterface 
     */
    protected $logger = null;
    
    /**
     * 
     * @param string $uri
     * @param string $token
     * @param string $scope
     */
    public function __construct(EntityManagerInterface $em, \Psr\Log\LoggerInterface $logger, $uri, $token, $scope, $channel, $giveableRoles, $allowedCommands, $aliases) {
        $this->em = $em;
        $this->uri = $uri;
        $this->token = $token;
        $this->scope = $scope;
        $this->channel = $channel;
        $this->giveableRoles = $giveableRoles;
        $this->allowedCommands = $allowedCommands;
        $this->aliases = $aliases;
        $this->logger = $logger;
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
            // var_dump($event);
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
                && preg_match('`^\.([a-zA-Z0-9]+)(( +)(.+))?$`', $data['content'], $matches)) { // @TODO better includes of "." as joker
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
        if($this->isAllowedCommand($cmd)) {
            try {
                $cmd = $this->getAliasedCommand($cmd);
                $o = DiscordCommands\DiscordCommand::load($cmd, $args, $pureData, $this->aliases);
                if(!empty($o)) {
                    $this->disableDelay();
                    $o->execute($this);
                    $this->disableDelay();
                } else {
                    $this->talk('Unimplemented command `'.$cmd.'`', $pureData['channel_id']);
                }
            } catch (Exception $ex) {
                $this->logger->critical($ex->getMessage());
                $this->talk('An error occured, please retry later', $pureData['channel_id']);
            }
        } else {
            $this->talk('Unrecognized command `'.$cmd.'`');
        }
    }
    
    /**
     * 
     * @param string $cmd
     * @return bool
     */
    public function isAllowedCommand(string $cmd): bool {
        return in_array($cmd, $this->allowedCommands);
    }
    
    /**
     * 
     * @return array
     */
    public function getAllowedCommands(): array {
        return $this->allowedCommands;
    }
    
    /**
     * 
     * @return array
     */
    public function getGiveableRolesNames(): array {
        return $this->giveableRoles;
    }
    
    /**
     * 
     * @param string $actualComd
     * @return string
     */
    public function getAliasedCommand(string $actualComd): string {
        return array_key_exists($actualComd, $this->aliases)? ($this->aliases[$actualComd]):$actualComd;
    }
    
    /**
     * 
     * @return $this
     */
    public function enableDelay() {
        $this->delayEnabled = true;
        return $this;
    }
    
    /**
     * 
     * @return $this
     */
    public function disableDelay() {
        $this->delayEnabled = false;
        return $this;
    }
    
    /**
     * 
     * @param type $channelId
     */
    public function flush($channelId = null) {
        $this->disableDelay();
        $this->talk(implode(PHP_EOL, $this->flushableTalks), $channelId);
        $this->flushableTalks = [];
    }
    
    /**
     * 
     * @param mixed $msg
     * @param ?string $channel
     */
    public function talk($msg, $channel = null) {
        if(empty($channel)) { $channel = $this->channel; }
        if($this->delayEnabled) {
            $this->flushableTalks[] = $msg;
        } else {
            $response = REST::json($this->uri, '/channels/'.$channel.'/messages', REST::METHOD_POST, [
                'content' => $msg,
            ], [
                'Authorization' => 'Bot '.$this->token,
            ]);
        }
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
        if(in_array($rolename, $this->getGiveableRolesNames())) {
            $rid = $this->getRoleId($rolename);
            if(!empty($rid)) {
                $returns = $rid;
            } // @TODO invalid role
        } // @TODO not giveable role
        return $returns;
    }
    
    /**
     * 
     * @param string $roleName
     * @param string $gid
     * @param string $user
     * @return bool
     */
    public function giveRole(string $roleName, string $gid, string $user): bool {
        $returns = false;
        $rid = $this->isGiveable($roleName);
        if(!empty($rid)) {
            $response = REST::json($this->uri, '/guilds/'.$gid.'/members/'.$user.'/roles/'.$rid, REST::METHOD_PUT, [], [
                'Authorization' => 'Bot '.$this->token,
            ]);
            $returns = (Response::HTTP_NO_CONTENT === $response->getCode());
        }
        return $returns;
    }
    
    /**
     * 
     * @param string $roleName
     * @param string $gid
     * @param string $user
     * @return bool
     */
    public function removeRole(string $roleName, string $gid, string $user): bool {
        $returns = false;
        $rid = $this->isGiveable($roleName);
        if(!empty($rid)) {
            $response = REST::json($this->uri, '/guilds/'.$gid.'/members/'.$user.'/roles/'.$rid, REST::METHOD_DELETE, [], [
                'Authorization' => 'Bot '.$this->token,
            ]);
            $returns = (Response::HTTP_NO_CONTENT === $response->getCode());
        }
        return $returns;
    }
    
    public function registerUser(int $id, string $name, int $disc): DiscordUser {
        $u = new DiscordUser;
        $u->setDateAdd(new DateTime);
        $u->setId($id);
        $u->setDiscordName($name);
        $u->setDiscriminator($disc);
        return $this->saveUser($u);
    }
    
    public function retrieveUser(int $id): ?DiscordUser {
        $u = $this->em->getRepository(DiscordUser::class)->find($id);
        return empty($u)? null:$u;
    }
    
    public function updateUserName(DiscordUser $u, string $name, int $disc): DiscordUser {
        $u->setDiscordName($name);
        $u->setDiscriminator($disc);
        return $this->saveUser($u);
    }
    
    public function findOrCreateUser(int $id, string $name, int $disc): DiscordUser {
        $u = $this->retrieveUser($id);
        if(empty($u)) {
            $u = $this->registerUser($id, $name, $disc);
        }
        return $u;
    }
    
    public function saveUser(DiscordUser $u) {
        $this->em->persist($u);
        $this->em->flush();
        return $u;
    }
    
    /**
     * 
     * @return EntityManagerInterface
     */
    public function getEntityManager() {
        return $this->em;
    }
}
