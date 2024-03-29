# hh_seo
hh_seo is a TYPO3 extension.
You can set your meta-tags in your FLUID-template and overwrite them in another template.
Without the hassle of having to edit any TypoScript files. Or having to look into the controllers.

### optional

* is compatible with [cs_seo](https://extensions.typo3.org/extension/cs_seo/) (but the frontend output is generated by the hh_seo extension) // See Todos


### Installation
... like any other TYPO3 extension

To use it in your FLUID theme:
- default ViewHelper NameSpace is "hhseo" so you can use this extension ViewHelpers like <hhseo:[viewHelperName] ... >

or

- set namespace like:
`<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
    xmlns:hhs="http://typo3.org/ns/HauerHeinrich/HhSeo/ViewHelpers"
    data-namespace-typo3-fluid="true">`

or

- set or copy the ViewHelper, examples are located at: "hh_seo/Resources/Public/Templates/..."


### Features
- No own TypoScript required
- No own php-script required
- only FLUID (ViewHelper)
- compatible with EXT:cs_seo


### Usage
ViewHelper attribute "order" (required):
- A higher "order" number overwrites lower ones.

ViewHelper attribute "dataType" should be set to "yaml".
(for legacy reasons - this will become the standard in the future! Currently INI-style is the default)

- Example useage in your theme:
    ```
    <seo:metaTag order="0" dataType="yaml">...</seo:metaTag>
    ```
    and EXT:news use a higher number e. g.

    ```
    <seo:metaTag order="10" dataType="yaml">...</seo:metaTag>
    ```

- [Example 1](Resources/Private/Templates/ExampleYamlStyle.1.html)
- [Example DEPRICATED 1](Resources/Private/Templates/Example.1.html)
- [Example DEPRICATED 2](Resources/Private/Templates/Example.2.html)

### Meta-Tags params:
- last-modified: uses timestamp as input

### Todos
- mayby add ViewHelper for every Meta-Tag
- restore compatibility with all fields added by EXT:cs_seo


### Deprecated
- OLD ini-style syntax


##### Copyright notice

This repository is part of the TYPO3 project. The TYPO3 project is
free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

The GNU General Public License can be found at
http://www.gnu.org/copyleft/gpl.html.

This repository is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

This copyright notice MUST APPEAR in all copies of the repository!

##### License
----
GNU GENERAL PUBLIC LICENSE Version 3
