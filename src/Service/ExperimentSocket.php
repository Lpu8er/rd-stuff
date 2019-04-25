<?php
namespace App\Service;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Description of ExperimentSocket
 *
 * @author lpu8er
 */
class ExperimentSocket {
    /**
     *
     * @var string 
     */
    protected $magic = null;
    
    /**
     *
     * @var string 
     */
    protected $secKey = null;
    
    public function __construct(string $magic) {
        $this->magic = $magic;
    }
    
    protected function computeKey($secKey) {
        return base64_encode(sha1($secKey.($this->magic), true)); // care : we need the raw output of sha1
    }
    
    public function handshake(Request $request): ?Response {
        $swk = $request->headers->get('Sec-WebSocket-Key');
        $swv = $request->headers->get('Sec-WebSocket-Version');
        $ups = $request->headers->get('Upgrade');
        if('websocket' === $ups) {
            $headers = [];
            $headers['Upgrade'] = 'websocket';
            $headers['Connection'] = 'Upgrade';
            $headers['Sec-WebSocket-Accept'] = $this->computeKey($swk);
            $returns = (new Response)->create('', Response::HTTP_SWITCHING_PROTOCOLS, $headers);
        }
        return $returns;
    }
}
