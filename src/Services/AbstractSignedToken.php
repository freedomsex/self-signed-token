<?php


namespace FreedomSex\Services;


abstract class AbstractSignedToken
{
    const TOKEN_REGEXP = '/[[:alnum:]-+=_]{1,40}\.\d+\.[[:alnum:]]{32}/';

    public $secretEnvName = 'APP_SECRET';
    public $tokenLifetime = 60;

    protected $secret;
    protected $time;
    protected $id = null;
    protected $bypass = false;

    public function __construct($tokenLifetime = null, $secret = null)
    {
        $this->secret = $secret ?? getenv($this->secretEnvName);
        $this->time = time();
        $this->setTtl($tokenLifetime);
    }

    public function created(): int
    {
        return $this->time;
    }

    public function setTtl($time = null)
    {
        if ($time) {
            $this->tokenLifetime = $time;
        }
    }

    public function setBypass(bool $value)
    {
        $this->bypass = ($value === true);
    }

    public function getId($token = null)
    {
        if ($token) {
            return $this->parse($token)[0];
        }
        return $this->id;
    }

    public function test($token): bool
    {
        if (preg_match(self::TOKEN_REGEXP, $token)) {
            return true;
        }
        return false;
    }

    public function parse($token)
    {
        if (!$this->test($token)) {
            throw new \TypeError('This sting does not match the token format');
        }
        $data = explode('.', $token);
        return $data;
    }

    public function generateId($prefix = ''): string
    {
        return md5($this->secret . $prefix . uniqid(mt_rand(1111, 8888), true));
    }

    public function sign(string $id, int $time): string
    {
        return md5(join(':', [
            $id,
            $time,
            $this->secret,
        ]));
    }

    public function expire(int $created): int
    {
        return $created + $this->tokenLifetime;
    }

    public function create($id = null, $expire = null): string
    {
        //
    }

    public function expired(int $expire, $ignore = false): bool
    {
        //
    }

    public function signed(string $token, bool $ignore = false): bool
    {
        if ($ignore) {
            return true;
        }
        if ($token) {
            list($id, $time, $sign) = $this->parse($token);
            if ($sign === $this->sign($id, $time)) {
                return true;
            }
        }
        return false;
    }

    public function valid(?string $token, bool $ignoreSign = false, bool $ignoreExpires = false)
    {
        if (!$token or !$this->test($token)) {
            return false;
        }
        [$id, $time] = $this->parse($token);
        $expired = $this->expired($time, $this->bypass ?? $ignoreExpires);
        $signed = $this->signed($token, $this->bypass ?? $ignoreSign);
        if ($id and $signed and !$expired) {
            return $id;
        }
        return false;
    }
}
