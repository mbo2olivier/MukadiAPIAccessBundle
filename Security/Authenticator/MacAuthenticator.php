<?php
/**
 * Created by PhpStorm.
 * User: Olivier
 * Date: 11/05/2017
 * Time: 15:11
 */

namespace Mukadi\APIAccessBundle\Security\Authenticator;


use Mukadi\APIAccessBundle\Model\ApiAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

abstract class MacAuthenticator extends ApiAuthenticator{

    public function createToken(Request $request, $providerKey)
    {
        if(!$request->query->has("client_id")){
            throw new BadCredentialsException("Client ID missing",ApiAuthenticator::UNKNOWN_REQUEST_SENDER);
        }else{
            $client_id = $request->query->get("client_id");
            if($client = $this->manager->find($client_id)){
                if(!$request->headers->has("Authorization")){
                    throw new BadCredentialsException("Unsigned request, missing authorization header",ApiAuthenticator::UNSIGNED_REQUEST);
                }
                $mac = $this->generateMACSignature($request,$client->getClientSecret());
                $auth = $request->headers->get("Authorization");
                $auth = (strpos($auth,"MAC ") == 0)? substr($auth,4):$auth;
                if(hash_equals($mac,$auth)){
                    return new PreAuthenticatedToken(
                        'anon.',
                        $client->getClientId(),
                        $providerKey
                    );
                }else{
                    throw new BadCredentialsException("Request signature is invalid",ApiAuthenticator::REQUEST_FAIL_AUTHENTICATED);
                }
            }else{
                throw new BadCredentialsException(sprintf('Cannot find client for ID "%s"',$client_id),ApiAuthenticator::UNKNOWN_REQUEST_SENDER);
            }
        }
    }

    /**
     * @param Request $request
     * @param string $secret
     * @return string
     */
    protected function generateMACSignature(Request $request,$secret){
        $data = strtolower($request->getMethod())."\n";
        foreach ($request->query->all() as $key => $val) {
            $data .= $val."\n";
        }
        return base64_encode(hash_hmac($this->getAlgorithm(),$data,$secret,true));
    }

    /**
     * @return string
     */
    abstract public function getAlgorithm();
} 