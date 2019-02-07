<?php

namespace App\Form\Model;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiForm
{
    /**
     * @var UploadedFile
     */
    private $app;

    /**
     * @var string
     */
    private $comment;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    private $commit;

    /**
     * @var string
     */
    private $jobName;

    /**
     * @var bool
     */
    private $ci;

    /**
     * @return UploadedFile
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * @param UploadedFile $app
     */
    public function setApp(UploadedFile $app)
    {
        $this->app = $app;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getRef()
    {
        return $this->ref;
    }

    /**
     * @param string $ref
     */
    public function setRef($ref)
    {
        $this->ref = $ref;
    }

    /**
     * @return string
     */
    public function getCommit()
    {
        return $this->commit;
    }

    /**
     * @param string $commit
     */
    public function setCommit($commit)
    {
        $this->commit = $commit;
    }

    /**
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }

    /**
     * @param string $jobName
     */
    public function setJobName($jobName)
    {
        $this->jobName = $jobName;
    }

    /**
     * @return bool
     */
    public function isCi()
    {
        return $this->ci;
    }

    /**
     * @param bool $ci
     */
    public function setCi($ci)
    {
        $this->ci = ($ci == 'true');
    }
}
