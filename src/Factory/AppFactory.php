<?php

namespace App\Factory;

use App\Entity\App;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Security\Core\Security;

class AppFactory
{
    /**
     * @var iterable<ReaderInterface>
     */
    private $readers;

    private $user;

    /**
     * AppFactory constructor.
     * @param iterable<ReaderInterface> $readers
     * @param Security $context
     */
    public function __construct(iterable $readers, Security $context)
    {
        $this->readers = $readers;
        $this->user = $context->getToken() !== null ? $context->getToken()->getUser() : null;
    }

    /**
     * @param UploadedFile $file
     * @param $comment
     * @param $project
     * @param $type
     * @param null $ref
     * @param null $commit
     * @param null $jobName
     * @param bool $ci
     * @return App
     * @throws \Exception
     */
    public function create(UploadedFile $file, $comment, $project, $type, $ref = null, $commit = null, $jobName = null, $ci = false)
    {
        $app = new App();
        $app->setComment($comment);
        $app->setBuild($file);
        $app->setCreated(new \DateTime());

        $app->setProjectId($project);
        $app->setCi($ci);
        $app->setRef($ref);
        $app->setCommit($commit);
        $app->setJobName($jobName);

        $app->setRelease(false);
        $app->setDebuggable(false);

        $app = $this->getData($app);

        $app->setCreatedName($this->user->getFirstName() . '  ' . $this->user->getLastName());
        $app->setCreatedId($this->user->getId());

        $app->setName($app->getBuild()->getClientOriginalName());

        return $app;
    }

    private function getData(App $app)
    {
        /** @var ReaderInterface $reader */
        foreach ($this->readers as $reader) {
            if (pathinfo($app->getBuild()->getClientOriginalName(), PATHINFO_EXTENSION) == $reader->getExtension()) {
                $app = $reader->readData($app);
                break;
            }
        }

        return $app;
    }
}
