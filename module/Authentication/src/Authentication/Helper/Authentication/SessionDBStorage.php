<?php
namespace Authentication\Helper\Authentication;

use Zend\Authentication\Storage;
use Zend\Session\SessionManager;

class SessionDBStorage extends Storage\Session
{
    /**
     *
     * @param string $namespace
     * @param string $member
     * @param SessionManager $manager
     */
    public function __construct($namespace = null, $member = null, SessionManager $manager = null)
    {
        parent::__construct($namespace, $member, $manager);
    }

    public function setRememberMe($rememberMe = 0, $time = 1209600)
    {
        if ($rememberMe == 1) {
            $this->session->getManager()->rememberMe($time);
        }
    }

    public function forgetMe()
    {
        $this->session->getManager()->forgetMe();
    }

    public function write($contents)
    {
        parent::write($contents);
        //var_dump($contents);
        /**
        when $this->authService->authenticate(); is valid, the session
        automatically called write('username')
        in this case, i want to save data like
        ["storage"] => array(4) {
        ["id"] => string(1) "1"
        ["username"] => string(5) "admin"
        ["ip_address"] => string(9) "127.0.0.1"
        ["user_agent"] => string(81) "Mozilla/5.0 (Macintosh; Intel Mac OS X 10.7;
        rv:18.0)
        Gecko/20100101    Firefox/18.0"
        }*/
        if (is_array($contents) && !empty($contents)) {
            $this->getSessionManager()
                ->getSaveHandler()
                ->write($this->getSessionId(), \Zend\Json\Json::encode($contents));
        }
    }

    public function clear()
    {
        $this->getSessionManager()->getSaveHandler()->destroy($this->getSessionId());
        parent::clear();
    }

    public function getSessionManager()
    {
        return $this->session->getManager();
    }

    public function getSessionId()
    {
        return $this->session->getManager()->getId();
    }

}