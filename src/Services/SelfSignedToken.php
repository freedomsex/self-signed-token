<?php


namespace FreedomSex\Services;


class SelfSignedToken extends AbstractSignedToken
{
    public function create($id = null, $expire = null): string
    {
        $this->id = $id ?: $this->generateId();
        $expire = $expire ?: $this->expire($this->time);
        return join('.', [
            $this->id,
            $expire,
            $this->sign($this->id, $expire),
        ]);
    }

    public function expired(int $expire, $ignore = false): bool
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
}
