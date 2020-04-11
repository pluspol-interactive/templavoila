<?php
declare(strict_types = 1);
namespace Ppi\TemplaVoilaPlus\Handler\Place;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\StringUtility;

use Ppi\TemplaVoilaPlus\Domain\Model\Place;
use Ppi\TemplaVoilaPlus\Domain\Model\TemplateYamlConfiguration;

abstract class AbstractYamlPlaceHandler extends AbstractPlaceHandler
{
    public const NAME = 'abstractYaml';

    protected $configurationClassName;

    protected function initializeConfigurations()
    {
        if ($this->configurations === null) {
            $this->configurations = [];
            $resourceFactory = \TYPO3\CMS\Core\Resource\ResourceFactory::getInstance();

            $filter = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Resource\Filter\FileExtensionFilter::class);
            // WTF? We cann't search for tvp.yaml so search for yaml and filter afterwards
            $filter->setAllowedFileExtensions('yaml');

            $folder = $resourceFactory->retrieveFileOrFolderObject($this->place->getPathAbsolute());
            $folder->setFileAndFolderNameFilters([[$filter, 'filterFileList']]);

            $files = $folder->getFiles(0, 0, \TYPO3\CMS\Core\Resource\Folder::FILTER_MODE_USE_OWN_FILTERS, true);

            foreach($files as $file) {
                if (StringUtility::endsWith($file->getName(), '.tvp.yaml')) {
                    try {
                        $configurationIdentifier = $this->getPlaceIdentifierFromFile($file);
                        $this->configurations[$configurationIdentifier] = new $this->configurationClassName($file, $configurationIdentifier);
                    } catch (\Exception $e) {
                        // Empty as we can't process this file
                        // @TODO Maybe report into log
                    }
                }
            }
        }
    }

    protected function getPlaceIdentifierFromFile($file): string
    {
        $identifier = $file->getIdentifier();
        $storageConfiguration = $file->getStorage()->getConfiguration();

        if ($file->getStorage()->getUid() === 0) {
            $relativePath = ltrim($file->getIdentifier(), '/');
            $identifier = mb_substr($relativePath, mb_strlen($this->place->getPathRelative()));
        } elseif ($storageConfiguration['pathType'] === 'relative') {
            $relativePath = rtrim($storageConfiguration['basePath'], '/') . '/' . ltrim($file->getIdentifier(), '/');
            $identifier = mb_substr($relativePath, mb_strlen($this->place->getPathRelative()));
        } else {
            throw new \Exception('Storage type not supported');
        }
        /** @TODO Absolute? Storage configuration */
        /**
         * Should we replace all Resource handlings with own file operations?
         * Its a mess with the handling ... how does this work in core:form?
         */

        return '/'. ltrim($identifier, '/');
    }
}

