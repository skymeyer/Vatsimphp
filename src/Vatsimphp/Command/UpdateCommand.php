<?php

/*
 * This file is part of the Vatsimphp package
 *
 * Copyright 2016 - Jelle Vink <jelle.vink@gmail.com>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

namespace Vatsimphp\Command;

use Vatsimphp\VatsimData;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

/**
 *
 * Update command to update local VATSIM data. Useful for cron runs.
 *
 */
class UpdateCommand extends Command
{
    /**
     * {inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('update')
            ->setDescription('Update VATSIM data from public servers.')
            ->addOption(
                'cacheDir',
                null,
                InputOption::VALUE_REQUIRED,
                'Cache directory, defaults to app/cache.'
            )
            ->addOption(
                'logFile',
                null,
                InputOption::VALUE_REQUIRED,
                'Log file location, defaults to app/logs/vatsimphp.log.'
            )
            ->addOption(
                'forceRefresh',
                null,
                InputOption::VALUE_NONE,
                'Force data refresh. Use with caution as you may get IP blocked.'
            )
        ;
    }

    /**
     * {inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $vatsim = new VatsimData();

        if ($input->hasArgument('cacheDir')) {
            $vatsim->setConfig('cacheDir', $input->getArgument('cacheDir'));
        }

        if ($input->hasArgument('logFile')) {
            $vatsim->setConfig('logFile', $input->getArgument('logFile'));
        }

        if ($input->hasArgument('forceRefresh')) {
            $vatsim->setConfig('forceDataRefresh', true);
        }

        $vatsim->loadData();

        $info = $vatsim->getGeneralInfo()->toArray();

        $output->writeln(sprintf(
            'VATSIM data up-to-date until %s',
            gmdate('Y-m-d H:i:s e', $info['update'])
        ));
    }
}
