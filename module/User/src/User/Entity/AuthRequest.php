<?php
namespace User\Entity\Google;

class AuthRequest
{

    public $auth_url;


    public function __construct( $url )
    {
        $this->auth_url = $url;
    }

    /**
     * @param mixed $auth_url
     */
    public function setAuthUrl($auth_url)
    {
        $this->auth_url = $auth_url;
    }

    /**
     * @return mixed
     */
    public function getAuthUrl()
    {
        return $this->auth_url;
    }

}
