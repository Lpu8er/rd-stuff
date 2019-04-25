<?php
namespace App\Service;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

use Symfony\Component\Security\Core\Security;

/**
 * Description of RatchetSocket
 *
 * @author lpu8er
 */
class RatchetSocket implements MessageComponentInterface {
    protected $securityService = null;
    protected $sessionService = null;
    protected $tokenService = null;
    
    protected $clients = null; // temp memory storage
    
    public function __construct(Security $securityService, \Symfony\Component\HttpFoundation\Session\SessionInterface $sessionService, \Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface $tokenService) {
        $this->securityService = $securityService;
        $this->sessionService = $sessionService;
        $this->tokenService = $tokenService;
        $this->clients = new \SplObjectStorage();
    }
    
    protected function loadSession(\GuzzleHttp\Psr7\Request $request) {
        $token = $this->getAuthToken($request);
        if(!empty($token)) {
            $this->tokenService->setToken($token); // will propagate an event
        }
    }
    
    protected function getAuthToken(\GuzzleHttp\Psr7\Request $request): ?\Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken {
        $returns = null;
        if($request->hasHeader('Cookie')) {
            $sessionId = preg_replace('`^PHPSESSID=(.+)$`', '$1', $request->getHeader('Cookie')[0]);
            if(!$this->sessionService->isStarted()) {
                $this->sessionService->setId($sessionId);
                $this->sessionService->start();
            }
            $returns = $this->sessionService->has('_security_main')? unserialize($this->sessionService->get('_security_main')):null;
        }
        return $returns;
    }
    
    protected function getCurrentUser() {
        $u = $this->securityService->getUser();
        return empty($u)? null:$u;
    }
    
    public function onOpen(ConnectionInterface $conn) {
        $this->loadSession($conn->httpRequest);
        echo 'opening connection for '.$conn->resourceId.' ...';
        $this->clients->attach($conn);
        echo ' opened'.PHP_EOL;
    }
    
    public function onClose(ConnectionInterface $conn) {
        $this->loadSession($conn->httpRequest);
        echo 'closing connection for '.$conn->resourceId.' ...';
        $this->clients->detach($conn);
        echo ' closed'.PHP_EOL;
    }
    
    public function onError(ConnectionInterface $conn, Exception $e) {
        $this->loadSession($conn->httpRequest);
        echo 'ERROR '.$e->getMessage().PHP_EOL;
        $conn->close();
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        $this->loadSession($from->httpRequest);
        echo 'receieved : '.$msg.' from '.$from->resourceId.PHP_EOL;
        foreach($this->clients as $c) {
            if($c !== $from) {
                $c->send($msg);
            }
        }
    }
}
