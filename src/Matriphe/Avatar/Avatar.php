<?php namespace Matriphe\Avatar;

use Avatarco;

class Avatar
{
    public function __construct()
    {
        $this->avatarco = new Avatarco;
    }

    public function create($string, $size = 100, $sprites = 0, $alpha = false)
    {
        $this->avatarco->init($string, $size, $sprites, $alpha);
    }

    public function show()
    {
        $this->avatarco->ShowPicture();
    }

    public function save($path = '/tmp')
    {
        return $this->avatarco->SavePicture($path);
    }
}