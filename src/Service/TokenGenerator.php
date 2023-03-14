<?php

namespace App\Service;

class TokenGenerator
{
    /**
     * @return string
     */
    public function generate(): string
    {
        return base_convert(sha1(uniqid(mt_rand(), true)), 16, 36);
    }
}
