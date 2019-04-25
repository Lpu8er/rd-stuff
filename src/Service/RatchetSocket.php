<?php
namespace App\Service;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

/**
 * Description of RatchetSocket
 *
 * @author lpu8er
 */
class RatchetSocket implements MessageComponentInterface {
    protected $clients = null; // temp memory storage
    
    public function __construct() {
        $this->clients = new \SplObjectStorage();
    }
    
    public function onOpen(ConnectionInterface $conn) {
        echo 'opening connection for '.$conn->resourceId.' ...';
        $this->clients->attach($conn);
        echo ' opened'.PHP_EOL;
    }
    
    public function onClose(ConnectionInterface $conn) {
        echo 'closing connection for '.$conn->resourceId.' ...';
        $this->clients->detach($conn);
        echo ' closed'.PHP_EOL;
    }
    
    public function onError(ConnectionInterface $conn, Exception $e) {
        echo 'ERROR '.$e->getMessage().PHP_EOL;
        $conn->close();
    }
    
    public function onMessage(ConnectionInterface $from, $msg) {
        echo 'receieved : '.$msg.' from '.$from->resourceId;
        $req = $from->httpRequest;
        var_dump($req);
        foreach($this->clients as $c) {
            if($c !== $from) {
                $c->send($msg);
            }
        }
    }
}
