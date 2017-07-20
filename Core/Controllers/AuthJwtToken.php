<?php
namespace DryMile\Core\Controller;

require_once __DIR__ ."/../Controller.php";
require_once __DIR__ ."/../Utils/JwtClaim.php";

use DryMile\Core as Core;

class AuthJwtTokenController extends Core\Controller
{
    protected $secret       = 'a-random-secret';
    protected $headerKey    = 'authorization';
    protected $claims       = [];
    protected $jwtId        = null;
    
    public function setSecret( $secret )                { $this->secret = "$secret"; }
    public function setJwtId( $jwtId )                  { $this->jwtId = "$jwtId"; }
    public function setContextClaim( $claims = [] )     { if ( is_array($claims) ) $this->claims = $claims; }
    public function addContextClaim( $name, $value )    { if ( !is_null($name) && !is_null($value) ) $this->claims[$name] = $value; }
    
    public function authenticate()
    {
        // Get the security token
        $token      = $this->router->getHeader( $this->headerKey );
        list($jwt)  = sscanf($token, 'Bearer %s'); 
        switch ( $jwt )
        {
            case '':
                $this->responder->header('x-authenticated: false');
                $response = $this->makeResponse( Core\ResponseCode::CODE_BADREQUEST );
                $response->addResponse( 'error', 'Missing Jwt Token' );
                break;
                
            default:
                $jwtClaim = new Core\Utils\JwtClaim();
                if ( $jwtClaim->decode( $jwt, $this->secret ) )
                {
                    // Are we active
                    if ( $jwtClaim->isActive() )
                    {
                        $response = $this->makeResponse( Core\ResponseCode::CODE_OK );
                    
                        // Do we need to check the ID?
                        if ( ! is_null($this->jwtId))
                        {
                            if ( $jwtClaim->getIdentifier() !== $this->jwtId )
                            {
                                $this->responder->header('x-authenticated: false');
                                $response = $this->makeResponse( Core\ResponseCode::CODE_UNAUTHORIZED );
                                $response->addResponse( 'error', 'Invalid Jwt Token ID' );
                            }
                        }
                        
                        if ( ! empty( $this->claims ) && $response->isOK() )
                        {
                            // We have some claims to check
                            foreach ( $this->claims as $name => $value )
                            {
                                if ( $jwtClaim->getContextClaim($name) !== $value )
                                {
                                    $this->responder->header('x-authenticated: false');
                                    $response = $this->makeResponse( Core\ResponseCode::CODE_UNAUTHORIZED );
                                    $response->addResponse( 'error', 'Invalid Jwt Token Claim' );
                                    break;
                                }
                            }
                        }
                    }
                    else
                    {
                        $this->responder->header('x-authenticated: false');
                        $response = $this->makeResponse( Core\ResponseCode::CODE_UNAUTHORIZED );
                        $response->addResponse( 'error', 'Invalid Jwt Token Not Active' );
                    }
                }
                else
                {
                    $this->responder->header('x-authenticated: false');
                    $response = $this->makeResponse( Core\ResponseCode::CODE_UNAUTHORIZED );
                    $response->addResponse( 'error', 'Invalid Jwt Token' );
                }
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
