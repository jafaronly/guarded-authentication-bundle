<?php
/**
 * Created by PhpStorm.
 * User: Jafar Jabr
 * Date: 12/21/2017
 * Time: 9:09 PM
 */

namespace Jafar\Bundle\GuardedAuthenticationBundle\Api\JWT\KeyLoader;

class LoadedJWS
{
    const VERIFIED = 'verified';
    const EXPIRED  = 'expired';
    const INVALID  = 'invalid';

    /**
     * @var array
     */
    private $payload;

    /**
     * @var string
     */
    private $state;

    /**
     * @var bool
     */
    private $hasLifetime;

    /**
     * @param array $payload
     * @param bool  $isVerified
     */
    public function __construct(array $payload, $isVerified, $hasLifetime = true)
    {
        $this->payload     = $payload;
        $this->hasLifetime = $hasLifetime;

        if (true === $isVerified) {
            $this->state = self::VERIFIED;
        }

        $this->checkIssuedAt();
        $this->checkExpiration();
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return bool
     */
    public function isVerified()
    {
        return self::VERIFIED === $this->state;
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        $this->checkExpiration();

        return self::EXPIRED === $this->state;
    }

    /**
     * @return bool
     */
    public function isInvalid()
    {
        return self::INVALID === $this->state;
    }

    /**
     * Ensures that the signature is not expired.
     */
    private function checkExpiration()
    {
        if (!$this->hasLifetime) {
            return;
        }

        if (!isset($this->payload['exp']) || !is_numeric($this->payload['exp'])) {
            return $this->state = self::INVALID;
        }

        if (0 <= (new \DateTime())->format('U') - $this->payload['exp']) {
            $this->state = self::EXPIRED;
        }
    }

    /**
     * Ensures that the iat claim is not in the future.
     */
    private function checkIssuedAt()
    {
        if (isset($this->payload['iat']) && (int) $this->payload['iat'] > time()) {
            return $this->state = self::INVALID;
        }
    }
}