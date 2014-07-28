<?php
/**
 *
 * Error Codes: 201 - 206
 */
namespace AG\NikePlusInterfaceBundle\Nike;

use OAuth\OAuth2\Token\TokenInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use AG\NikePlusInterfaceBundle\Nike\Exception as NikeException;

/**
 * Class AuthenticationGateway
 *
 * @package AG\NikePlusInterfaceBundle\Nike
 *
 * @since 0.0.1
 */
class AuthenticationGateway extends EndpointGateway
{
    /**
     * Determine if this user is authorised with Fitbit
     *
     * @access public
     * @version 0.0.1
     *
     * @throws NikeException
     * @return bool
     */
    public function isAuthorized()
    {
        try
        {
            return $this->service->getStorage()->hasAccessToken('Nike');
        }
        catch (\Exception $e)
        {
            throw new NikeException('Could not find the access token.', 206, $e);
        }
    }

    /**
     * Initiate the login process
     *
     * @access public
     * @version 0.0.1
     *
     * @throws NikeException
     * @return void
     */
    public function initiateLogin()
    {
        $url = $this->service->getAuthorizationUri();
        if (!filter_var($url, FILTER_VALIDATE_URL)) throw new NikeException('Nike+ returned an invalid login URL ('.$url.').', 201);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Authenticate user, request access token.
     *
     * @access public
     * @version 0.5.2
     *
     * @param string $code
     * @param string $state
     * @throws NikeException
     * @return TokenInterface
     */
    public function authenticateUser($code, $state = null)
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Authenticating User', 'Nike+ API');

        try
        {
            /** @var TokenInterface $tokenSecret */
            $tokenSecret = $this->service->getStorage()->retrieveAccessToken('Nike');
        }
        catch (\Exception $e)
        {
            $timer->stop('Authenticating User');
            throw new NikeException('Could not retrieve the access token secret.', 202, $e);
        }

        try
        {
            /** @var TokenInterface $tokenResponse */
            $tokenResponse = $this->service->requestAccessToken(
                $code,
                $state
            );
            $timer->stop('Authenticating User');
            return $tokenResponse;
        }
        catch (\Exception $e)
        {
            $timer->stop('Authenticating User');
            throw new NikeException('Unable to request the access token.', 203, $e);
        }
    }

    /**
     * Reset session
     *
     * @access public
     * @version 0.0.1
     *
     * @todo Need to add clear to the interface for phpoauthlib (this item was here when this project was branched)
     *
     * @throws NikeException
     * @return void
     */
    public function resetSession()
    {
        /** @var Stopwatch $timer */
        $timer = new Stopwatch();
        $timer->start('Resetting Session', 'Nike+ API');

        try
        {
            $this->service->getStorage()->clearToken('Nike');
        }
        catch (\Exception $e)
        {
            $timer->stop('Resetting Session');
            throw new NikeException('Could not clear the token.', 204);
        }
        $timer->stop('Resetting Session');
    }

    /**
     * Verify the token
     *
     * @access protected
     * @version 0.0.1
     *
     * @throws Exception
     * @return bool
     */
    protected function verifyToken()
    {
        if (!$this->isAuthorized()) throw new NikeException('Token could not be verified.', 205);
        return true;
    }
}
