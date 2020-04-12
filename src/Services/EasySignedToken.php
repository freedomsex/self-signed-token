<?php


namespace FreedomSex\Services;


class EasySignedToken extends AbstractSignedToken
{
    public function create($id = null, $created = null): string
    {
        $this->id = $id ?: $this->generateId();
        $created = $created ?: $this->time;
        return join('.', [
            $this->id,
            $created,
            $this->sign($this->id, $created),
        ]);
    }

    public function expired(int $created, $ignore = false): bool
    {
        if ($ignore) {
            return false;
        }
        if ($created) {
            if ($this->expire($created) > $this->time) {
                return false;
            }
        }
        return true;
    }
}
