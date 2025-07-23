<?php

namespace Visol\Viresponsiveimages\ViewHelpers;

/*                                                                        *
 * This script is part of the TYPO3 project - inspiring people to share!  *
 *                                                                        *
 * TYPO3 is free software; you can redistribute it and/or modify it under *
 * the terms of the GNU General Public License version 2 as published by  *
 * the Free Software Foundation.                                          *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        */

use TYPO3\CMS\Extbase\Service\ImageService;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractTagBasedViewHelper;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper;
use Visol\Viresponsiveimages\Service\SrcSetService;

class ResponsiveImageViewHelper extends AbstractTagBasedViewHelper
{

    /**
     * allow multidigit ratio values like 1024:768 or 4:3
     */
    const RATIO_PATTERN = '/(\d+):(\d+)/';

    /**
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @var SrcSetService
     */
    protected $srcSetService;

    protected ImageService $imageService;

    /**
     * @param SrcSetService $srcSetService
     */
    public function injectSrcSetService(SrcSetService $srcSetService)
    {
        $this->srcSetService = $srcSetService;
    }

    public function __construct()
    {
        parent::__construct();
        $this->imageService = GeneralUtility::makeInstance(ImageService::class);
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
        $this->registerArgument('additionalAttributes', 'array', 'Additional tag attributes. They will be added directly to the resulting HTML tag.', false);
        $this->registerArgument('data', 'array', 'Additional data-* attributes. They will each be added with a "data-" prefix.', false);

        $this->registerArgument('alt', 'string', 'Specifies an alternate text for an image', false);

        $this->registerArgument(
            'src',
            'string',
            'a path to a file, a combined FAL identifier or an uid (int).
            If $treatIdAsReference is set, the integer is considered the uid of the sys_file_reference record.
            If you already got a FAL object, consider using the $image parameter instead',
            false,
            ''
        );
        $this->registerArgument('treatIdAsReference', 'bool', 'given src argument is a sys_file_reference record', false, false);
        $this->registerArgument('image', 'object', 'a FAL object');

        $this->registerArgument('crop', 'string|bool', 'overrule cropping of image (setting to FALSE disables the cropping set in FileReference)');
        $this->registerArgument('cropVariant', 'string', 'select a cropping variant, in case multiple croppings have been specified or stored in FileReference', false, 'default');

        $this->registerArgument('sizes', 'string', 'Comma-separated list of image sizes');
        $this->registerArgument('ratio', 'string', 'Ratio of the image. This can be a float value (e.g. 1.5) or a ratio string (e.g. 1:2)');
        $this->registerArgument('maxWidth', 'int', 'maximum width of the image');
        $this->registerArgument('maxHeight', 'int', 'maximum height of the image');
        $this->registerArgument('absolute', 'bool', 'Force absolute URL', false, false);
    }

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     *
     * @throws Exception
     * @return string Rendered tag
     */
    public function render()
    {
        if (is_numeric($this->arguments['ratio'])) {
            $ratio = $this->arguments['ratio'];
        } elseif (preg_match(static::RATIO_PATTERN, $this->arguments['ratio'], $matches)) {
            $ratio = $matches[1] / $matches[2];
        }

        $maximumWidth = $this->arguments['maxWidth'];
        $maximumHeight = $this->arguments['maxHeight'];
        $crop = $this->arguments['crop'];
        $cropVariant = $this->arguments['cropVariant'];

        if (($this->arguments['src'] === '' && is_null($this->arguments['image'])) || ($this->arguments['src'] !== '' && !is_null($this->arguments['image']))) {
            throw new Exception('You must either specify a string src or a File object.', 1382284106);
        }

        try {
            $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);

            if ($this->arguments['sizes']) {
                $sizesCsv = $this->arguments['sizes'];
            } else {
                $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
                $extbaseFrameworkConfiguration = $configurationManager->getConfiguration(ConfigurationManagerInterface::CONFIGURATION_TYPE_FULL_TYPOSCRIPT);
                $sizesCsv = $extbaseFrameworkConfiguration['config.']['responsiveImage.']['sizes'];
            }
            $sizes = GeneralUtility::intExplode(',', $sizesCsv, true);

            $srcSetString = $this->srcSetService->getSrcSetAttribute($image, $ratio, $maximumWidth, $maximumHeight, $crop, $cropVariant, $sizes, $this->arguments['absolute']);
            $classNames = ['lazyload'];
            if (isset($this->arguments['class'])) {
                $classNames[] = $this->arguments['class'];
            }

            $this->tag->addAttributes(
                [
                    'class' => implode(' ', $classNames),
                    'data-sizes' => 'auto',
                    'data-srcset' => $srcSetString,
                    'srcset' => 'data:image/gif;base64,R0lGODlhAQABAIAAAP///wAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw=='
                ]
            );

            $alt = $image->getProperty('alternative');
            $title = $image->getProperty('title');

            // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
            if (empty($this->arguments['alt'])) {
                $this->tag->addAttribute('alt', $alt);
            }
            // render title attribute only if title is set as viewhelper argument
            if (isset($this->arguments['title']) && !empty(trim($this->arguments['title']))) {
                $this->tag->addAttribute('title', $this->arguments['title']);
            }
            else {
                // remove title attribute set by registerUniversalTagAttributes() to avoid empty " " title attribute
                $this->tag->removeAttribute('title');
            }
        } catch (ResourceDoesNotExistException $e) {
            // thrown if file does not exist
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
        }

        return $this->tag->render();
    }
}
