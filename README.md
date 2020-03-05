Responsive Images Extension
===

### JavaScripts

#### Static

The extension uses lazysizes.js along with the plugins respimg/ls.respimg.min.js ls.parent-fit.min.js
and ls.bgset.min.js which are all included Resources/Private and referenced by

```
page.includeJSFooterlibs {
	lazysizes1respimg = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/respimg/ls.respimg.min.js
	lazysizes2parentfit = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/parent-fit/ls.parent-fit.min.js
	lazysizes3bgset = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/plugins/bgset/ls.bgset.min.js
	lazysizes9core = EXT:viresponsiveimages/Resources/Public/Javascripts/lazysizes/lazysizes.min.js
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
