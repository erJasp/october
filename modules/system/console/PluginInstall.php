<?php namespace System\Console;

use Illuminate\Console\Command;
use System\Classes\UpdateManager;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use System\Classes\PluginManager;

class PluginInstall extends Command
{

    /**
     * The console command name.
     * @var string
     */
    protected $name = 'plugin:install';

    /**
     * The console command description.
     * @var string
     */
    protected $description = 'Adds a new plugin.';

    /**
     * Create a new command instance.
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     * @return void
     */
    public function fire()
    {
        $pluginName = $this->argument('name');
        $manager = UpdateManager::instance()->resetNotes();

        $pluginDetails = $manager->requestPluginDetails($pluginName);

        $code = array_get($pluginDetails, 'code');
        $hash = array_get($pluginDetails, 'hash');

        $this->output->writeln(sprintf('<info>Downloading plugin: %s</info>', $code));
        $manager->downloadPlugin($code, $hash);

        $this->output->writeln(sprintf('<info>Unpacking plugin: %s</info>', $code));
        $manager->extractPlugin($code, $hash);

        /*
         * Migrate plugin
         */
        $this->output->writeln(sprintf('<info>Migrating plugin...</info>', $code));
        PluginManager::instance()->loadPlugins();
        $manager->updatePlugin($code);

        foreach ($manager->getNotes() as $note)
            $this->output->writeln($note);
    }

    /**
     * Get the console command arguments.
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the plugin. Eg: AuthorName.PluginName'],
        ];
    }

    /**
     * Get the console command options.
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }

}