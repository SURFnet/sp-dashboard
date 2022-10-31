<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new League\Tactician\Bundle\TacticianBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Stfalcon\Bundle\TinymceBundle\StfalconTinymceBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new OpenConext\MonitorBundle\OpenConextMonitorBundle(),
            new Surfnet\SamlBundle\SurfnetSamlBundle(),
            new Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DashboardBundle(),
            new Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\DashboardSamlBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Nelmio\SecurityBundle\NelmioSecurityBundle(),
        ];

        // The LexikTranslationBundle should be loaded *after* the
        // FrameworkBundle so it can override the default translator.
        $bundles[] = new Lexik\Bundle\TranslationBundle\LexikTranslationBundle();

        if (in_array($this->getEnvironment(), ['dev', 'test'], true)) {
            $bundles[] = new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
        }

        return $bundles;
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        $env = $this->getEnvironment();
        // For test and dev, use a cache folder outside the project folder.
        if ($env === 'dev' || $env === 'test') {
            return '/tmp/sp-dashboard/' . $env;
        }
        return dirname(__DIR__).'/var/cache/'.$env;
    }

    public function getLogDir()
    {
        return dirname(__DIR__).'/var/logs';
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir().'/config/config_'.$this->getEnvironment().'.yml');
    }
}
