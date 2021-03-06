<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudDocker\Config\Source;

use Composer\Semver\Semver;
use Illuminate\Config\Repository;
use Magento\CloudDocker\App\ConfigurationMismatchException;
use Magento\CloudDocker\Filesystem\FileList;
use Magento\CloudDocker\Filesystem\Filesystem;
use Magento\CloudDocker\Service\ServiceFactory;
use Magento\CloudDocker\Service\ServiceInterface;
use Symfony\Component\Yaml\Yaml;
use Exception;

/**
 * Source to read Magento Cloud configs
 */
class CloudSource implements SourceInterface
{
    /**
     * @var FileList
     */
    private $fileList;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var ServiceFactory
     */
    private $serviceFactory;

    /**
     * @var array
     */
    private static $map = [
        ServiceInterface::SERVICE_DB => ['db', 'database', 'mysql'],
        ServiceInterface::SERVICE_DB_QUOTE => ['mysql-quote'],
        ServiceInterface::SERVICE_DB_SALES => ['mysql-sales'],
        ServiceInterface::SERVICE_ELASTICSEARCH => ['elasticsearch', 'es'],
        ServiceInterface::SERVICE_OPENSEARCH => ['opensearch', 'os'],
        ServiceInterface::SERVICE_REDIS => ['redis'],
        ServiceInterface::SERVICE_RABBITMQ => ['rmq', 'rabbitmq']
    ];

    /**
     * @param FileList $fileList
     * @param Filesystem $filesystem
     * @param ServiceFactory $serviceFactory
     */
    public function __construct(
        FileList $fileList,
        Filesystem $filesystem,
        ServiceFactory $serviceFactory
    ) {
        $this->fileList = $fileList;
        $this->filesystem = $filesystem;
        $this->serviceFactory = $serviceFactory;
    }

    /**
     * @inheritDoc
     */
    public function read(): Repository
    {
        $appConfigFile = $this->fileList->getAppConfig();
        $servicesConfigFile = $this->fileList->getServicesConfig();

        if (!$this->filesystem->exists($appConfigFile) || !$this->filesystem->exists($servicesConfigFile)) {
            return new Repository();
        }

        try {
            $appConfig = Yaml::parse(
                $this->filesystem->get($this->fileList->getAppConfig())
            );
        } catch (\Exception $exception) {
            throw new SourceException($exception->getMessage(), $exception->getCode(), $exception);
        }

        if (!isset($appConfig['type'])) {
            throw new SourceException('PHP version could not be parsed.');
        }

        if (!isset($appConfig['relationships'])) {
            throw new SourceException('Relationships could not be parsed.');
        }

        if (!isset($appConfig['name'])) {
            throw new SourceException('Name could not be parsed.');
        }

        [$type, $version] = explode(':', $appConfig['type']);
        /**
         * RC versions are not supported
         */
        $version = rtrim($version, '-rc');

        if ($type !== ServiceInterface::SERVICE_PHP) {
            throw new SourceException(sprintf(
                'Type "%s" is not supported',
                $type
            ));
        }

        $repository = new Repository();

        $repository = $this->addPhp(
            $repository,
            $version,
            $appConfig['runtime']['extensions'] ?? [],
            $appConfig['runtime']['disabled_extensions'] ?? []
        );
        $repository = $this->addXdebug(
            $repository,
            $version
        );
        $repository = $this->addMailhog($repository);
        $repository = $this->addCronJobs(
            $repository,
            $appConfig['crons'] ?? []
        );
        $repository = $this->addMounts(
            $repository,
            $appConfig['mounts'] ?? []
        );
        $repository = $this->addRelationships(
            $repository,
            $appConfig['relationships'] ?? []
        );
        $repository = $this->addName(
            $repository,
            $appConfig['name']
        );
        $repository->set(self::HOOKS, $appConfig['hooks'] ?? []);

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param array $relationships
     * @return Repository
     * @throws SourceException
     */
    private function addRelationships(Repository $repository, array $relationships): Repository
    {
        $servicesConfig = $this->getServiceConfig();

        foreach ($relationships as $constraint) {
            [$name] = explode(':', $constraint);

            if (!isset($servicesConfig[$name]['type'])) {
                throw new SourceException(sprintf(
                    'Service with name "%s" could not be parsed',
                    $name
                ));
            }

            $version = explode(':', $servicesConfig[$name]['type'])[1];

            foreach (self::$map as $service => $possibleNames) {
                if (!in_array($name, $possibleNames, true)) {
                    continue;
                }

                if ($service !== ServiceInterface::SERVICE_DB
                    && $repository->has(self::SERVICES . '.' . $service)
                ) {
                    throw new SourceException(sprintf(
                        'Only one instance of service "%s" supported',
                        $service
                    ));
                }

                try {
                    $repository->set([
                        self::SERVICES . '.' . $service . '.enabled' => true,
                        self::SERVICES . '.' . $service . '.version' => $version,
                        self::SERVICES . '.' . $service . '.image' => $this->serviceFactory->getDefaultImage($service)
                    ]);

                    if (isset($servicesConfig[$name]['configuration'])) {
                        $repository->set(
                            self::SERVICES . '.' . $service . '.configuration',
                            $servicesConfig[$name]['configuration']
                        );
                    }
                } catch (ConfigurationMismatchException $exception) {
                    throw new SourceException($exception->getMessage(), $exception->getCode(), $exception);
                }
            }
        }

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param string $version
     * @param array $extensions
     * @param array $disabledExtensions
     * @return Repository
     * @throws ConfigurationMismatchException
     */
    private function addPhp(
        Repository $repository,
        string $version,
        array $extensions,
        array $disabledExtensions
    ): Repository {
        $repository->set([
            self::PHP_ENABLED => true,
            self::PHP_VERSION => $version
        ]);

        if ($extensions) {
            /* Parse nested Blackfire configuration */
            foreach ($extensions as $key => $phpExtension) {
                if (is_array($phpExtension) && $phpExtension['name'] === ServiceInterface::SERVICE_BLACKFIRE) {
                    /* Store extension data in repository */
                    $repository->set([
                        self::SERVICES_BLACKFIRE_ENABLED => true,
                        self::SERVICES_BLACKFIRE_VERSION =>
                                $this->serviceFactory->getDefaultVersion(ServiceInterface::SERVICE_BLACKFIRE),
                        self::SERVICES_BLACKFIRE_IMAGE =>
                                $this->serviceFactory->getDefaultImage(ServiceInterface::SERVICE_BLACKFIRE),
                        self::SERVICES_BLACKFIRE_CONFIG => $phpExtension['configuration']
                    ]);
                    /* Reset nested extension data with extension name */
                    $extensions[$key] = ServiceInterface::SERVICE_BLACKFIRE;
                }
            }

            $repository[self::PHP_ENABLED_EXTENSIONS] = $extensions;
        }

        if ($disabledExtensions) {
            $repository[self::PHP_DISABLED_EXTENSIONS] = $disabledExtensions;
        }

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param string $version
     * @return Repository
     * @throws SourceException
     */
    private function addXdebug(Repository $repository, string $version): Repository
    {
        try {
            $repository->set([
                self::SERVICES_XDEBUG . '.enabled' => false,
                self::SERVICES_XDEBUG . '.image' => $this->serviceFactory->getDefaultImage(
                    ServiceInterface::SERVICE_FPM_XDEBUG
                ),
                self::SERVICES_XDEBUG . '.version' => $version
            ]);
        } catch (ConfigurationMismatchException $exception) {
            throw new SourceException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param array $jobs
     * @return Repository
     * @throws SourceException
     */
    private function addCronJobs(Repository $repository, array $jobs): Repository
    {
        $preparedJobs = [];

        foreach ($jobs as $name => $config) {
            if (!isset($config['spec'], $config['cmd'])) {
                throw new SourceException(sprintf(
                    'One of "%s" cron job properties is not define',
                    $name
                ));
            }

            $preparedJobs[$name] = [
                'schedule' => $config['spec'],
                'command' => $config['cmd']
            ];
        }

        if ($preparedJobs) {
            $repository->set([
                self::CRON_JOBS => $preparedJobs
            ]);
        }

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param array $mounts
     * @return Repository
     */
    private function addMounts(Repository $repository, array $mounts): Repository
    {
        foreach ($mounts as $mountName => $mountData) {
            $repository->set(self::MOUNTS . '.' . $mountName, [
                'path' => $mountName,
                'orig' => $mountData
            ]);
        }

        return $repository;
    }

    /**
     * @param Repository $repository
     * @param string $name
     * @return Repository
     */
    private function addName(Repository $repository, string $name): Repository
    {
        $repository->set([
            self::NAME => $name,
        ]);

        return $repository;
    }

    /**
     * Adds mailhog configuration
     *
     * @param Repository $repository
     * @return Repository
     */
    private function addMailhog(Repository $repository): Repository
    {
        $repository->set([
            self::SERVICES_MAILHOG . '.enabled' => true
        ]);

        return $repository;
    }

    /**
     * Returns config from services yaml
     *
     * @return array
     * @throws SourceException
     */
    private function getServiceConfig(): array
    {
        try {
            $servicesConfig = Yaml::parse(
                $this->filesystem->get($this->fileList->getServicesConfig())
            );
        } catch (Exception $exception) {
            throw new SourceException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return is_array($servicesConfig) ? $servicesConfig : [];
    }
}
