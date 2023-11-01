Responsive Images for TYPO3 CMS
===============================

This extensions provides a `ResponsiveImageViewHelper` and a `SrcSetViewHelper` to provide responsive images based on
the srcset approach. It supports crop variants, but also defining an own ratio.

While srcset is [supported in most current browsers](https://caniuse.com/srcset), for background images a similar approach
with a custom `data-bgset` attributes can be used.

## Compatibility and Maintenance

This package is currently maintained for the following versions:

| TYPO3 Version         | Package Version | Branch  | Maintained    |
|-----------------------|-----------------|---------|---------------|
| TYPO3 11.5.x          | 2.x             | master  | Yes           |
| TYPO3 10.4.x          | 1.x             | -       | No            |
| TYPO3 9.5.x           | 0.9.x           | -       | No            |

### Usage in Fluid

This example is showing the setup for a header image, added in the page properties in a desktop and mobile version.

Add ```{namespace viresp=Visol\Viresponsiveimages\ViewHelpers}```
to your Fluid template

```
<f:if condition="{page.files}">
    <f:if condition="{page.files.0.properties.crop}">
        <f:then>
            <div class="img-desktop hidden-xs">
            <viresp:responsiveImage
                    cropVariant="headerImageDesktop"
                    treatIdAsReference="true"
                    src="{page.files.0.uid}"
                    class="img-cropped"
                    alt="{page.files.0.properties.alternative}"
                    additionalAttributes="{data-sizes: 'auto'}"
            />
            </div>
        </f:then>
        <f:else>
            <div class="img-desktop hidden-xs">
                <f:comment>
                    no image cropping has been seleted in the editor:
                    the image will be cropped in the given ratio relative to the center
                </f:comment>
                <viresp:responsiveImage
                    treatIdAsReference="true"
                    ratio="1170:200"
                    src="{page.files.0.uid}"
                    class="img-autocropped"
                    alt="{page.files.0.properties.alternative}"
                    additionalAttributes="{data-sizes: 'auto'}"
            />
            </div>
        </f:else>
    </f:if>
</f:if>
```

The corresponding image editor setup in ```YourSitePackage/Configuration/TCA/Overrides/pages.php``` would look like this:

```
$GLOBALS['TCA']['pages']['types'][(string)\TYPO3\CMS\Frontend\Page\PageRepository::DOKTYPE_DEFAULT]['columnsOverrides']['media']['config']['overrideChildTca']['columns']['crop']['config']['cropVariants'] = [
    'headerImageDesktop' => [
        'title' => 'Header Image Desktop',
        'allowedAspectRatios' => [
            '1170:200' => [
                'title' => 'Letterbox Slot 1170:200',
                'value' => 1170 / 200,
            ]
        ],
        'coverAreas' => [
            [
                'x' => 0.02,
                'y' => 0.66,
                'width' => 0.96,
                'height' => 0.34,
            ]
        ]
    ],
];
``` 

### JavaScripts

If you want to support older browsers (e.g. IE) and/or use the bgset feature to create responsive background images,
you need to use the lazysizes library.

#### JavaScript bundled with this extension

The extension uses lazysizes.js along with the plugins respimg/ls.respimg.min.js, ls.parent-fit.min.js
and ls.bgset.min.js which are all included in `Resources/Private` and included by

```
page.includeJSFooterlibs {
	lazysizes1respimg = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/respimg/ls.respimg.min.js
	lazysizes2parentfit = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/parent-fit/ls.parent-fit.min.js
	lazysizes3bgset = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/bgset/ls.bgset.min.js
	lazysizes4core = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/lazysizes.min.js
}
```

#### Install using npm

The preferred way to include those libraries would be to disable the includes in your setup through the following
TypoScript configuration

```
page.includeJSFooterlibs {
	lazysizes1respimg >
	lazysizes2parentfit >
	lazysizes3bgset >
	lazysizes9core >
}
```

and install lazysizes through npm/yarn in your project

```
npm install --save lazysizes
```

or add lazysizes to the dependencies section of your package.json

```
"dependencies": {
   ...
   "lazysizes": "^5.1.1",
   ...
}
```

Then choose your favourite way to build/include the sources from there.
With **webpack** you would import the libraries in your main.js like:

```
import 'lazysizes/plugins/respimg/ls.respimg';
import 'lazysizes/plugins/parent-fit/ls.parent-fit';
import 'lazysizes/plugins/bgset/ls.bgset';
import 'lazysizes/lazysizes';
```

### Credits

https://www.npmjs.com/package/lazysizes

visol digitale Dienstleistungen GmbH, www.visol.ch
