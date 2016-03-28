<?php

require_once __DIR__ . "/vendor/autoload.php";

class Kerenl
{
    public function execute()
    {
        $lj = new \LJ\Worlds\Controller();

        return $lj->doExcute();
    }
}