<?php


namespace FreedomSex\Services;


class SelfSignedToken
{
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

    public function setTtl(int $time)
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

    public function sign($id, $created)
    {
        return md5(join(':', [
            $id,
            $created,
            $this->secret,
        ]));
    }

    public function create($id = null, $created = null)
    {
        $this->id = $id ?: $this->generateId();
        $created = $created ?: $this->time;
        return join('.', [
            $this->id,
            $created,
            $this->sign($this->id, $created),
        ]);
    }

    public function expired($created, $ignore = false)
    {
        if ($ignore) {
            return false;
        }
        if ($created) {
            if ($created >= ($this->time - $this->ttl)) {
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
            list($id, $created, $sign) = $this->parse($token);
            if ($sign === $this->sign($id, $created)) {
                return true;
            }
        }
        return false;
    }

    public function valid($token, $ignoreSign = false, $ignoreExpires = false)
    {
        list($id, $created) = $this->parse($token);
        $expired = $this->expired($created, $this->bypass ?? $ignoreExpires);
        $signed = $this->signed($token, $this->bypass ?? $ignoreSign);
        if ($id and $signed and !$expired) {
            return $id;
        }
        return false;
    }
}
