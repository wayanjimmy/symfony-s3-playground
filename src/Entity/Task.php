<?php

namespace App\Entity;

class Task
{
    protected $task;
    protected $media;

    public function getTask()
    {
        return $this->task;
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia($media)
    {
        $this->media = $media;
    }
}
