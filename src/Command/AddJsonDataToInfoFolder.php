<?php

namespace App\Command;

use App\Services\AppDataManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class AddJsonDataToInfoFolder extends Command
{
    /**
     * @var AppDataManager
     */
    private $dataManager;

    /**
     * AddJsonInfoToS3 constructor.
     * @param AppDataManager $dataManager
     */
    public function __construct(AppDataManager $dataManager)
    {
        $this->dataManager = $dataManager;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:add:json_data:to_info_folder');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $i = $this->dataManager->saveDataToInfoFolder();
            $io->success('Was moved ' . $i . ' files');
        } catch (\Exception $exception) {
        }
    }
}
