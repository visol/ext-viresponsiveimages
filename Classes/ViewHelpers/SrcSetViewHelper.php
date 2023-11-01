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
use Visol\Viresponsiveimages\Service\SrcSetService;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\Exception;

/**
 * Resizes a given image (if required) and renders the respective img tag
 *
 * = Examples =
 *
 * <code title="Default">
 * <f:image src="EXT:myext/Resources/Public/typo3_logo.png" alt="alt text" />
 * </code>
 * <output>
 * <img alt="alt text" src="typo3conf/ext/myext/Resources/Public/typo3_logo.png" width="396" height="375" />
 * or (in BE mode):
 * <img alt="alt text" src="../typo3conf/ext/viewhelpertest/Resources/Public/typo3_logo.png" width="396" height="375" />
 * </output>
 *
 * <code title="Image Object">
 * <f:image image="{imageObject}" />
 * </code>
 * <output>
 * <img alt="alt set in image record" src="fileadmin/_processed_/323223424.png" width="396" height="375" />
 * </output>
 *
 * <code title="Inline notation">
 * {f:image(src: 'EXT:viewhelpertest/Resources/Public/typo3_logo.png', alt: 'alt text', minWidth: 30, maxWidth: 40)}
 * </code>
 * <output>
 * <img alt="alt text" src="../typo3temp/assets/images/f13d79a526.png" width="40" height="38" />
 * (depending on your TYPO3s encryption key)
 * </output>
 *
 * <code title="Other resource type (e.g. PDF)">
 * <f:image src="fileadmin/user_upload/example.pdf" alt="foo" />
 * </code>
 * <output>
 * If your graphics processing library is set up correctly then it will output a thumbnail of the first page of your PDF document.
 * <img src="fileadmin/_processed_/1/2/csm_example_aabbcc112233.gif" width="200" height="284" alt="foo">
 * </output>
 *
 * <code title="Non-existent image">
 * <f:image src="NonExistingImage.png" alt="foo" />
 * </code>
 * <output>
 * Could not get image resource for "NonExistingImage.png".
 * </output>
 */
class SrcSetViewHelper extends AbstractTagBasedViewHelper
{

    public const RATIO_PATTERN = '/(\d):(\d)/';

    public function __construct(
        protected ImageService $imageService,
        protected SrcSetService $srcSetService
    )
    {
        parent::__construct();
    }

    /**
     * Initialize arguments.
     */
    public function initializeArguments()
    {
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
        $this->registerArgument('maxWidth', 'int', 'minimum width of the image');
        $this->registerArgument('maxHeight', 'int', 'minimum width of the image');
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
        } catch (ResourceDoesNotExistException $e) {
            // thrown if file does not exist
        } catch (\UnexpectedValueException $e) {
            // thrown if a file has been replaced with a folder
        } catch (\RuntimeException $e) {
            // RuntimeException thrown if a file is outside of a storage
        } catch (\InvalidArgumentException $e) {
            // thrown if file storage does not exist
        }

        return $srcSetString;
    }
}
