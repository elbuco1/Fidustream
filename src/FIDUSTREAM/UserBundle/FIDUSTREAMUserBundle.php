<?php

namespace FIDUSTREAM\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class FIDUSTREAMUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
