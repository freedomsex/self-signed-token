<?php


namespace FreedomSex\Services;


class SelfSignedToken
{
    const TOKEN_REGEXP = '/[[:alnum:]]{1,32}\.\d+\.[[:alnum:]]{32}/';

    public $ttl = 60;

    private $id = null;
    private $bypass = false;

    public function __construct($ttl = null, $secret = null)
    {
        $this->secret = $secret ?? getenv('APP_SECRET');
        $this->time = time();
        $this->setTtl($ttl);
    }

    public function created()
    {
        return $this->time;
    }

    public function setTtl($time = null)
    {
        if ($time) {
            $this->ttl = $time;
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

    public function parse($token)
    {
        $data = explode('.', $token);
        if (count($data) == 3) {
            return $data;
        }
        return false;
    }

    public function generateId($prefix = '')
    {
        return md5($this->secret . $prefix . uniqid(mt_rand(1111, 8888), true));
    }

    public function sign($id, $expire)
    {
        return md5(join(':', [
            $id,
            $expire,
            $this->secret,
        ]));
    }

    public function create($id = null, $expire = null)
    {
        $this->id = $id ?: $this->generateId();
        $expire = $expire ?: $this->expire($this->time);
        return join('.', [
            $this->id,
            $expire,
            $this->sign($this->id, $expire),
        ]);
    }

    public function expire($created)
    {
        return $created + $this->ttl;
    }

    public function expired($expire, $ignore = false)
    {
        if ($ignore) {
            return false;
        }
        if ($expire) {
            if ($expire > $this->time) {
                return false;
            }
        }
        return true;
    }

    public function signed($token, $ignore = false)
    {
        if ($ignore) {
            return true;
        }
        if ($token) {
            list($id, $expire, $sign) = $this->parse($token);
            if ($sign === $this->sign($id, $expire)) {
                return true;
            }
        }
        return false;
    }

    public function valid($token, $ignoreSign = false, $ignoreExpires = false)
    {
        list($id, $expire) = $this->parse($token);
        $expired = $this->expired($expire, $this->bypass ?? $ignoreExpires);
        $signed = $this->signed($token, $this->bypass ?? $ignoreSign);
        if ($id and $signed and !$expired) {
            return $id;
        }
        return false;
    }
}
