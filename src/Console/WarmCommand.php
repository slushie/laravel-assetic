<?php namespace Slushie\LaravelAssetic\Console;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class WarmCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'asset:warm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate asset output to disk.';

    /**
     * Execute the console command.
     */
    public function fire()
    {
        $assets = $this->laravel['asset'];
        $group = $this->argument('group');

        if (is_null($group)) {
            $group = $assets->listGroups();
        } else {
            $group = (array) $group;
        }

        foreach ($group as $name) {
            $this->info("Generating asset group '$name'");
            $collection = $assets->createGroup($name, $this->option('overwrite'));

            $this->line('Wrote output to public/'.$collection->getTargetPath());
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'group',
                InputArgument::OPTIONAL,
                'Name of the asset group to warm.',
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            [
                'overwrite',
                null,
                InputOption::VALUE_NONE,
                'Force overwrite of output.',
            ],
        ];
    }
}
