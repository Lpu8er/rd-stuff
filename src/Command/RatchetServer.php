<?php

namespace App\Command;

use App\Service\RatchetSocket;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of RatchetServer
 *
 * @author lpu8er
 */
class RatchetServer extends Command {

    protected $socketService = null;

    public function __construct(RatchetSocket $socketService) {
        $this->socketService = $socketService;
        parent::__construct();
    }

    protected function configure() {
        $this->setName('ratchet:start');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        IoServer::factory(
                new HttpServer(
                        new WsServer(
                                $this->socketService
                        )
                ),
                8080
        )->run();
    }

}
