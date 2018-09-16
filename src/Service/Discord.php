<?php
namespace App\Service;

/**
 * Description of Discord
 *
 * @author lpu8er
 */
class Discord {
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
     * @var int
     */
    protected $permissions;
    /**
     *
     * @var string
     */
    protected $clientId;
    
    /**
     * 
     * @param string $uri
     * @param string $scope
     * @param string $permissions
     * @param string $clientId
     */
    public function __construct($uri, $scope, $permissions, $clientId) {
        $this->uri = $uri;
        $this->scope = $scope;
        $this->permissions = $permissions;
        $this->clientId = $clientId;
    }
}
