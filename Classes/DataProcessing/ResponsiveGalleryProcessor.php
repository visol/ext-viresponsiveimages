<?php
namespace Visol\Viresponsiveimages\DataProcessing;

use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
use TYPO3\CMS\Frontend\DataProcessing\GalleryProcessor;

/**
 * passing imageorient to fluid where a custom viewhelper handles the output of sourcesets
 * based on several settings and can be used for f.i. the CType "textmedia"
 */
class ResponsiveGalleryProcessor extends GalleryProcessor
{

    /**
     * Matching the tt_content field towards the imageOrient option
     *
     * @var array
     */
    protected $availableGalleryPositions = [
        /*'horizontal' => [
            'center' => [0, 8],
            'right' => [1, 9, 17, 25],
            'left' => [2, 10, 18, 26]
        ],
        'vertical' => [
            'above' => [0, 1, 2],
            'intext' => [17, 18, 25, 26],
            'below' => [8, 9, 10]
        ],*/
        'vertical' => [
            'above' => [100, 101, 102],
            'intext' => [120, 121, 122, 123, 130, 131, 132, 133],
            'beside' => [140, 141, 142, 143, 150, 151, 152, 153],
            'below' => [110, 111, 112, 113],
            'gallery' => [160, 161, 162, 163]
        ]
    ];

    /**
     * Storage for processed data
     *
     * @var array
     */
    protected $galleryData = [
        'position' => [
            'horizontal' => '',
            'vertical' => '',
            'noWrap' => false,
            'imageorient' => 100
        ],
        'width' => 0,
        'count' => [
            'files' => 0,
            'columns' => 0,
            'rows' => 0,
        ],
        'columnSpacing' => 0,
        'border' => [
            'enabled' => false,
            'width' => 0,
            'padding' => 0,
        ],
        'rows' => []
    ];

    /**
     * Process data for a gallery, for instance the CType "textmedia"
     *
     * @param ContentObjectRenderer $cObj The content object renderer, which contains data of the content element
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     * @throws ContentRenderingException
     */
    public function process(
        ContentObjectRenderer $cObj,
        array $contentObjectConfiguration,
        array $processorConfiguration,
        array $processedData
    ) {
        if (isset($processorConfiguration['if.']) && !$cObj->checkIf($processorConfiguration['if.'])) {
            return $processedData;
        }

        $this->contentObjectRenderer = $cObj;
        $this->processorConfiguration = $processorConfiguration;

        $filesProcessedDataKey = (string)$cObj->stdWrapValue(
            'filesProcessedDataKey',
            $processorConfiguration,
            'files'
        );
        if (isset($processedData[$filesProcessedDataKey]) && is_array($processedData[$filesProcessedDataKey])) {
            $this->fileObjects = $processedData[$filesProcessedDataKey];
            $this->galleryData['count']['files'] = count($this->fileObjects);
        } else {
            throw new ContentRenderingException('No files found for key ' . $filesProcessedDataKey . ' in $processedData.', 1436809789);
        }

        $this->numberOfColumns = (int)$this->getConfigurationValue('numberOfColumns', 'imagecols');
        $this->mediaOrientation = (int)$this->getConfigurationValue('mediaOrientation', 'imageorient');
        $this->maxGalleryWidth = (int)$this->getConfigurationValue('maxGalleryWidth') ?: 600;
        $this->maxGalleryWidthInText = (int)$this->getConfigurationValue('maxGalleryWidthInText') ?: 300;
        $this->equalMediaHeight = (int)$this->getConfigurationValue('equalMediaHeight', 'imageheight');
        $this->equalMediaWidth = (int)$this->getConfigurationValue('equalMediaWidth', 'imagewidth');
        $this->columnSpacing = (int)$this->getConfigurationValue('columnSpacing');
        $this->borderEnabled = (bool)$this->getConfigurationValue('borderEnabled', 'imageborder');
        $this->borderWidth = (int)$this->getConfigurationValue('borderWidth');
        $this->borderPadding = (int)$this->getConfigurationValue('borderPadding');
        $this->cropVariant = $this->getConfigurationValue('cropVariant') ?: 'default';

        $this->galleryData['position']['imageorient'] = $this->mediaOrientation;

        $this->determineGalleryPosition();
        $this->determineMaximumGalleryWidth();

        $this->calculateRowsAndColumns();
        $this->calculateMediaWidthsAndHeights();

        $this->prepareGalleryData();

        $targetFieldName = (string)$cObj->stdWrapValue(
            'as',
            $processorConfiguration,
            'gallery'
        );

        $processedData[$targetFieldName] = $this->galleryData;

        return $processedData;
    }
}
