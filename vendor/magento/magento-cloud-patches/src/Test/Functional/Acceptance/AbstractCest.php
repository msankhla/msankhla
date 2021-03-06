<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CloudPatches\Test\Functional\Acceptance;

/**
 * Abstract class with implemented before/after Cest steps.
 */
class AbstractCest
{
    /**
     * @var string
     */
    protected $edition = 'EE';

    /**
     * @param \CliTester $I
     */
    public function _before(\CliTester $I): void
    {
        $I->cleanupWorkDir();
    }

    /**
     * @param \CliTester $I
     * @param string $templateVersion
     * @param string $magentoVersion
     */
    protected function prepareTemplate(\CliTester $I, string $templateVersion, string $magentoVersion = null): void
    {
        $I->cloneTemplateToWorkDir($templateVersion);
        $I->createAuthJson();
        $I->createArtifactsDir();
        $I->createArtifactCurrentTestedCode('patches', '1.0.99');
        $I->addArtifactsRepoToComposer();
        $I->addDependencyToComposer('magento/magento-cloud-patches', '1.0.99');

        $I->addEceDockerGitRepoToComposer();
        $I->addQualityPatchesGitRepoToComposer();
        $I->addEceToolsGitRepoToComposer();
        $I->addCloudComponentsGitRepoToComposer();

        $dependencies = [
            'magento/magento-cloud-docker',
            'magento/magento-cloud-components',
            'magento/quality-patches',
            'magento/ece-tools',
        ];

        foreach ($dependencies as $dependency) {
            $I->assertTrue(
                $I->addDependencyToComposer($dependency, $I->getDependencyVersion($dependency)),
                'Can not add dependency ' . $dependency
            );
        }

        if ($this->edition === 'CE' || $magentoVersion) {
            $version = $magentoVersion ?: $this->getVersionRangeForMagento($I);
            $I->removeDependencyFromComposer('magento/magento-cloud-metapackage');
            $I->addDependencyToComposer(
                $this->edition === 'CE' ? 'magento/product-community-edition' : 'magento/product-enterprise-edition',
                $version
            );
        }

        $I->composerUpdate();
    }

    /**
     * @param array $data
     * @return string
     */
    protected function convertEnvFromArrayToJson(array $data): string
    {
        return addslashes(json_encode($data));
    }

    /**
     * @param \CliTester $I
     * @return string
     */
    protected function getVersionRangeForMagento(\CliTester $I): string
    {
        $composer = json_decode(file_get_contents($I->getWorkDirPath() . '/composer.json'), true);

        return $composer['require']['magento/magento-cloud-metapackage'] ?? '';
    }

    /**
     * @param \CliTester $I
     */
    public function _after(\CliTester $I): void
    {
        $I->stopEnvironment();
        $I->removeWorkDir();
    }
}
