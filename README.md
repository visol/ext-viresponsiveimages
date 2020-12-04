Responsive Images Extension
===

### JavaScripts

#### Static

The extension uses lazysizes.js along with the plugins respimg/ls.respimg.min.js ls.parent-fit.min.js
and ls.bgset.min.js which are all included Resources/Private and referenced by

```
page.includeJSFooterlibs {
	lazysizes1respimg = EXT:viresponsiveimages/Resources/Private/Javascripts/lazysizes/plugins/respimg/ls.respimg.min.js
	lazysizes2parentfit = EXT:viresponsiveimages/Resources/Private/Javascripts/lazysizes/plugins/parent-fit/ls.parent-fit.min.js
	lazysizes3bgset = EXT:viresponsiveimages/Resources/Private/Javascripts/lazysizes/plugins/bgset/ls.bgset.min.js
	lazysizes9core = EXT:viresponsiveimages/Resources/Private/Javascripts/lazysizes/lazysizes.min.js
}
```

#### Install using npm

The prefered way to include those libraries would be to disable the includes in your setup by

```
page.includeJSFooterlibs {
	lazysizes1respimg >
	lazysizes2parentfit >
	lazysizes3bgset >
	lazysizes9core >
}
```

and install lazysizes by npm in your project

```
npm install --save lazysizes
```

or add lazysizes to the dependencies section of your packackge.json

```
"dependencies": {
   ...
   "lazysizes": "^5.1.1",
   ...
}
```

then choose your favourite way to build/include the sources from there.
With **webpack** you would import the libraries in your main.js like:

```
import 'lazysizes/plugins/respimg/ls.respimg';
import 'lazysizes/plugins/parent-fit/ls.parent-fit';
import 'lazysizes/plugins/bgset/ls.bgset';
import 'lazysizes/lazysizes';
```

#### Usage Example

This example ist showing the setup for a header image, added in the page properties in a desktop and mobile version.

Add ```{namespace viresp=Visol\Viresponsiveimages\ViewHelpers}```
 to your html template 
 
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
            <div class="img-mobile hidden-sm hidden-md hidden-lg">
                <f:comment>
                    mobile image:
                    if set in editor 'teaserImageMobile' cropping is applied.
                    if not, the image is cropped in the given ratio from the center
                </f:comment>
                <viresp:responsiveImage
                        cropVariant="headerImageMobile"
                        treatIdAsReference="true"
                        ratio="4:2"
                        sizes="320,640"
                        src="{page.files.0.uid}"
                        class="img-autocropped"
                        alt="{page.files.0.properties.alternative}"
                        additionalAttributes="{data-sizes: 'auto'}"
                />
            </div>
        </f:then>
        <f:else>
            <div class="img-desktop hidden-xs">
                <f:comment>
                    no image cropping as ben seleted in the editor:
                    the image weill be cropped in the given ratio relative to the center
                    for desktop the full range of sizes will be generated
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
            <div class="img-mobile hidden-sm hidden-md hidden-lg">
                <f:comment>
                    no image cropping as ben seleted in the editor:
                    the image weill be cropped in the given ratio relative to the center
                    for mobile only the given sizes (comma separated) will be generated
                </f:comment>
                <viresp:responsiveImage
                        treatIdAsReference="true"
                        ratio="4:2"
                        sizes="320,640"
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
    'headerImageMobile' => [
        'title' => 'Header Image Mobile',
        'allowedAspectRatios' => [
            '4:2' => [
                'title' => 'Square 4:2',
                'value' => 4 / 2,
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
    ]
];
``` 



