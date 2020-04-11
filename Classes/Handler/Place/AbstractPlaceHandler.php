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

abstract class AbstractPlaceHandler
{
    public const NAME = 'abstract';

    /**
     * @var Place
     */
    protected $place;

    /**
     * @var array|null Runtime cache for loaded template configurations
     */
    protected $configurations;

    public function __construct(Place $place)
    {
        $this->place = $place;
    }

    public function getConfigurations(): array
    {
        $this->initializeConfigurations();
        return $this->configurations;
    }

    public function getConfiguration(string $identifier)/** @TODO Configuration master class?: TemplateYamlConfiguration */
    {
        $this->initializeConfigurations();

        if (isset($this->configurations[$identifier])) {
            return $this->configurations[$identifier];
        }

        throw new \Exception('Configuration with identifer "' . $identifier . '" not found');
    }

    abstract protected function initializeConfigurations();


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
