<?php
require_once __DIR__.'/vendor/autoload.php';

class Robofile extends \Robo\Tasks
{
    protected $docs = [
        'docs/Test.md' => 'AspectMock\Test',
        'docs/ClassProxy.md' => 'AspectMock\Proxy\ClassProxy',
        'docs/InstanceProxy.md' => 'AspectMock\Proxy\InstanceProxy'
    ];

    public function release()
    {
        $this->say("Releasing AspectMock");

        $this->taskGit()
            ->add('CHANGELOG.md')
            ->commit('updated')
            ->push()
            ->run();

        $this->taskGitHubRelease(file_get_contents('VERSION'))
            ->uri('Codeception/AspectMock')
            ->askDescription()
            ->run();
    }

    public function docs()
    {
        foreach ($this->docs as $file => $class) {
            $this->taskGenDoc($file)
                ->docClass($class)
                ->processMethod(
                    function (\ReflectionMethod $m, $doc) {
                        $doc = str_replace(array('@since'), array(' * available since version'), $doc);
                        $doc = str_replace(array(' @', "\n@"), array("  * ", "\n * "), $doc);
                        return $doc;
                    }
                )->run();
        }
    }

    public function added($addition)
    {
        $this->taskChangelog()
            ->version(file_get_contents('VERSION'))
            ->change($addition)
            ->run();
    }

    public function bump($version = null)
    {
        if (!$version) {
            $versionParts = explode('.', file_get_contents('VERSION'));
            $versionParts[count($versionParts)-1]++;
            $version = implode('.', $versionParts);
        }
        $this->taskWriteToFile('VERSION')
            ->line($version)
            ->run();
    }

}