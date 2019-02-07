<?php

namespace App\Command;

use App\Services\AppDataManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ParameterType;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddJsonInfoToS3 extends Command
{
    const LIMIT_PAGINATION = 50;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var AppDataManager
     */
    private $dataManager;



    /**
     * AddJsonInfoToS3 constructor.
     * @param RegistryInterface $doctrine
     * @param AppDataManager $dataManager
     */
    public function __construct(RegistryInterface $doctrine, AppDataManager $dataManager)
    {
        $this->doctrine = $doctrine;
        $this->dataManager = $dataManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:add:json_info');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $sqlCount = "SELECT COUNT(*) AS number_of_rows FROM app";

        $sql = "SELECT 
          app.id AS `id`,
          app.name AS `name`,
          app.version AS version,
          app.buildNumber AS buildNumber,
          app.bundleId AS bundleId,
          app.minSdkLevel AS minSdkLevel,
          app.permssions AS permission,
          app.debuggable AS debuggable,
          app.bundleName AS bundleName,
          app.bundleVersion AS bundleVersion,
          app.minimumOSVersion AS minimumOSVersion,
          app.platformVersion AS platformVersion,
          app.bundleIdentifier AS bundleIdentifier,
          app.bundleDisplayName AS bundleDisplayName,
          app.bundleShortVersionString AS bundleShortVersionString,
          app.bundleSupportedPlatforms AS bundleSupportedPlatforms,
          app.supportedInterfaceOrientations AS supportedInterfaceOrientations,
          app.size AS size,
          app.type AS type,
          app.buildUrl AS buildUrl,
          app.qrcodeUrl AS qrcodeUrl,
          app.createdName AS createdName,
          app.createdId AS createdId,
          app.projectId AS projectId,
          app.iconUrl AS iconUrl,
          app.released AS `release`,
          app.token AS token,
          app.created AS created,
          app.comment AS `comment`,
          app.ci AS ci,
          app.ref AS ref,
          app.commit AS `commit`,
          app.job_name AS jobName,
          app.app_server AS appServer,
          app.plistUrl AS plistUrl
        FROM app 
        ORDER BY app.id ASC 
        LIMIT ? 
        OFFSET ?;";

        try {
            /** @var Connection $conn */
            $conn = $this->doctrine->getConnection();

            $statement = $conn->prepare($sqlCount);
            $statement->execute();

            $numberOfRows = (int) $statement->fetch(0)['number_of_rows'];

            $numberPages = (int) ceil($numberOfRows / self::LIMIT_PAGINATION);

            $statement = $conn->prepare($sql);

            for ($i = 0; $i < $numberPages; $i++) {
                $statement->bindValue(1, self::LIMIT_PAGINATION, ParameterType::INTEGER);
                $statement->bindValue(2, $i * self::LIMIT_PAGINATION, ParameterType::INTEGER);
                $statement->execute();

                $data = $statement->fetchAll();
                $this->dataManager->saveJsonDataFromArray($data);
            }

            $conn->close();
            $io->success('Number of apps: ' . $numberOfRows);
        } catch (\Exception $exception) {
            $conn->close();
            $io->error($exception->getMessage());
        }
    }
}
