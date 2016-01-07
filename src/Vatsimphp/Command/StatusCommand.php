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
use Symfony\Component\Console\Helper\Table;

/**
 *
 * Status command for cached data.
 *
 */
class StatusCommand extends Command
{
    /**
     * {inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('status')
            ->setDescription('Display current VATSIM data statistics')
            ->addOption(
                'cacheDir',
                null,
                InputOption::VALUE_REQUIRED,
                'Cache directory, defaults to app/cache.'
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

        $vatsim->setConfig('cacheOnly', true);
        $vatsim->loadData();

        $info = $vatsim->getGeneralInfo()->toArray();

        $table = new Table($output);
        $table->addRows(array(
            $info
        ));

        $table->render();
    }
}
