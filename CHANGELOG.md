## Unreleased

[FEATURE] Add `eager` argument to the ResponsiveImageViewHelper: renders a real srcset, src, width and height with `fetchpriority="high"` instead of lazy-loading via lazysizes, e.g. for the LCP image. `eagerSizes` sets the sizes attribute (default `100vw`).
[BUGFIX] Return an empty srcset from the SrcSetViewHelper instead of an undefined variable when the image cannot be processed

## 3.0.0 (2025-04-01)

[BREAKING] Don't set a title tag from the image properties by default. Pass the title as an additional viewhelper attribute if needed.

## 2.0.0 (2022-12-14)

[BUGFIX] Use absolute image URLs in SrcSet ViewHelper if requested
[TASK] Follow-up: Fix include of TCAdefaults.typoscript
[TASK] Fix include of TCAdefaults.typoscript
[BUGFIX] Use absolute image URLs in data-srcset if requested
[TASK] TYPO3 rector
[TASK] TYPO3 v11 compatibility
