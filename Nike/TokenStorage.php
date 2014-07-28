<?php
/**
 *
 * Error Codes: 1401-1403
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use OAuth\OAuth2\Token\StdOAuth2Token;
use OAuth\Common\Storage\Memory;
use OAuth\Common\Storage\Session;
use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class TokenStorage
 *
 * @package AG\NikePlusInterfaceBundle\Nike
 *
 * @since 0.0.1
 */
class TokenStorage
{
    /**
     * @var StdOAuth2Token
     */
    protected $token;
    /**
     * @var Memory
     */
    protected $adapter;

    /**
     * Constructor for the token storage
     *
     * @access public
     * @version 0.0.1
     *
     * @param string $storage The storage to use for the token
     * @param string $token  The token to be added to the storage if this is pre-authorised
     * @param string $secret The secret associated with the token.
     * @throws NikeException
     */
    public function __construct($storage = 'memory', $accessToken = null, $lifetime = null, $refreshToken = null)
    {
        try
        {
            $this->token = new StdOAuth2Token();
        }
        catch(\Exception $e)
        {
            throw new NikeException('Could not create token.', 1401, $e);
        }
        if ($storage == 'memory') $this->adapter = new Memory();
        elseif ($storage == 'session') $this->adapter = new Session();
        else throw new NikeException('Invalid token storage provider.', 1402);

        if ($accessToken !== null && $lifetime !== null)
        {
            try
            {
                $this->token->setAccessToken($accessToken);
                $this->token->setLifetime($lifetime);

                if($refreshToken !== null)
                {
                    $this->token->setRefreshToken($refreshToken);
                }

                $this->adapter->storeAccessToken('Nike', $this->token);
            }
            catch(\Exception $e)
            {
                throw new NikeException('Could not store token details.', 1403, $e);
            }
        }
    }

    /**
     * Get the storage adapter
     *
     * @access public
     * @version 0.0.1
     *
     * @return Memory|Session
     */
    public function getAdapter()
    {
        return $this->adapter;
    }
}
