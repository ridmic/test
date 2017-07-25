<?php namespace DryMile\Core\Controller;

require_once __DIR__ ."/../Controller.php";

use DryMile\Core as Core;

class AuthApiKeyController extends Core\Controller
{
    protected $apiKey    = 'a-random-api-key';
    protected $apiHeader = 'x-api-key';
    
    public function setApiKey( $apiKey )
    {
        $this->apiKey = "$apiKey";
    }
    
    public function authenticate()
    {
        // Get the security token
        $token = $this->router->getHeader( $this->apiHeader );
        switch ( $token )
        {
            case '':
                $this->responder->header('x-authenticated: false');
                $response = $this->makeResponse( Core\ResponseCode::CODE_FORBIDDEN );
                $response->addResponse( 'error', 'Missing API Key' );
                break;
                
            case $this->apiKey:
                $response = $this->makeResponse( Core\ResponseCode::CODE_OK );
                break;
                
            default:
                $this->responder->header('x-authenticated: false');
                $response = $this->makeResponse( Core\ResponseCode::CODE_UNAUTHORIZED );
                $response->addResponse( 'error', 'Invalid API Key' );
                break;
        }
        return $response;
    }

    // Overrides
    protected function registerRoutes()
    {
        $this->addBefore( 'ALL', '{:any}', [$this, 'authenticate' ] );        
    }
}
