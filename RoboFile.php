<?php
class Robofile extends \Robo\Tasks
{
    protected $docs = [
        'docs/Test.md' => 'AspectMock\Test',
        'docs/ClassProxy.md' => 'AspectMock\Proxy\ClassProxy',
        'docs/InstanceProxy.md' => 'AspectMock\Proxy\InstanceProxy'
    ];

    protected function version()
    {
        return file_get_contents(__DIR__.'/VERSION');
    }

    public function release()
    {
        $this->say("Releasing AspectMock");

        $this->test();

        $this->docs();
        
        $this->taskGit()
            ->add('CHANGELOG.md')
            ->commit('updated')
            ->push()
            ->run();

        $this->taskGitHubRelease($this->version())
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
            ->version($this->version())
            ->change($addition)
            ->run();
    }

    public function bump($version = null)
    {
        if (!$version) {
            $versionParts = explode('.', $this->version());
            $versionParts[count($versionParts)-1]++;
            $version = implode('.', $versionParts);
        }

        file_put_contents('VERSION', $version);
    }

    public function test()
    {
        $res = $this->taskCodecept()->run();
        if (!$res) {
            $this->say('Tests didnt pass, release declined');
            exit;
        }
    }
}