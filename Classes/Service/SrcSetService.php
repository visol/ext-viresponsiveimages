<?php
namespace Visol\Viresponsiveimages\Service;

/*
 * This file is inspired by the Visol.Neos.ResponsiveImages package.
 *
 * (c) visol digitale Dienstleistungen GmbH, www.visol.ch
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use TYPO3\CMS\Core\Imaging\ImageManipulation\CropVariantCollection;
use TYPO3\CMS\Core\Resource\AbstractFile;
use TYPO3\CMS\Core\Resource\FileInterface;
use TYPO3\CMS\Extbase\Service\ImageService;

/**
 * Render the srcset attribute with responsive images. Accepts mostly the same parameters as the uri.image ViewHelper of the Neos.Media package:
 * asset, maximumWidth, maximumHeight, allowCropping, ratio.
 */
class SrcSetService
{

    /**
     * @var ImageService
     */
    protected $imageService;

    /**
     * @param ImageService $imageService
     */
    public function injectImageService(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a processed image path
     *
     * @param FileInterface $image
     * @param float $ratio
     * @param int $maximumWidth
     * @param int $maximumHeight
     * @param string $crop
     * @param string $cropVariant
     * @param array $sizes
     * @param boolean $absolute
     * @return string
     * @throws \Exception
     */
    public function getSrcSetAttribute($image, $ratio, $maximumWidth, $maximumHeight, $crop, $cropVariant, array $sizes, $absolute = false)
    {
        if (!is_array($sizes) || !count($sizes) > 0) {
            throw new \Exception('No sizes defined.', 1519837126);
        }

        if (!$image instanceof FileInterface) {
            throw new \Exception('No asset given for rendering.', 1519844659);
        }

        if ($image->getProperty('type') == AbstractFile::FILETYPE_IMAGE) {
            $assetWidth = $image->getProperty('width');
            $assetHeight = $image->getProperty('height');
        }

        $cropString = $crop;
        if ($cropString === null && $image->hasProperty('crop') && $image->getProperty('crop')) {
            $cropString = $image->getProperty('crop');
        }
        $cropVariantCollection = CropVariantCollection::create((string)$cropString);
        $cropVariant = $cropVariant ?: 'default';
        $cropArea = $cropVariantCollection->getCropArea($cropVariant);

        $srcSetData = [];
        foreach ($sizes as $size) {
            $currentWidth = null;
            $currentMaximumWidth = $size;
            $currentHeight = null;
            $currentMaximumHeight = null;

            if ($currentMaximumWidth > $assetWidth) {
                continue;
            }

            if (isset($maximumWidth) && $currentMaximumWidth > $maximumWidth) {
                continue;
            }

            if ($ratio) {
                $currentWidth = $currentMaximumWidth;
                $currentMaximumHeight = floor($size / $ratio);
                $currentHeight = $currentMaximumHeight;

                if ($currentMaximumHeight > $assetHeight) {
                    continue;
                }

                if (isset($maximumHeight) && $currentMaximumHeight > $maximumHeight) {
                    continue;
                }
            }

            $processingInstructions = [
                'width' => $currentWidth . 'c',
                'height' => $currentHeight . 'c',
                'maxWidth' => $currentMaximumWidth,
                'maxHeight' => $currentMaximumHeight,
                'crop' => $cropArea->isEmpty() ? null : $cropArea->makeAbsoluteBasedOnFile($image),
            ];

            $processedImage = $this->imageService->applyProcessingInstructions($image, $processingInstructions);
            $imageUri = $this->imageService->getImageUri($processedImage, $absolute);

            if ($imageUri === null) {
                continue;
            }

            $srcSetData[] = $imageUri . ' ' . $processedImage->getProperty('width') . 'w ' . $processedImage->getProperty('height') . 'h ';
        }

        return implode(', ', $srcSetData);
    }
}
