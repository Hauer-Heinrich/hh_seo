# hh_seo
hh_seo is a TYPO3 extension.
You can set your meta-tags in your FLUID-template, and overwrite them in a other template, for example:
You have a default page-template and generate your meta-tags with hh_seo.
Then you want overwrite your default page-meta-tags with one from tx_news or other extensions, then you can set:
a) "order"-ViewHelper parameter = merge meta-tags from all templates but overwrites tags which has the highest order number (order is required)
b) "overwrite"-ViewHelper parameter = clear all meta-tags from templates with lower order number (optional)

### optional

* [cs_seo] - works well with: https://extensions.typo3.org/extension/cs_seo/ (only for the Backend - FE-output is disabled)


### Installation
... like any other TYPO3 extension

To use it in your FLUID theme:
- set namespace like:
`<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
    xmlns:hhs="http://typo3.org/ns/HauerHeinrich/HhSeo/ViewHelpers"
    data-namespace-typo3-fluid="true">`

- set or copy the ViewHelper, examples: "hh_seo/Resources/Public/Templates/..."


### Features
- No own TypoScript required
- No own php-script required
- only FLUID (ViewHelper)
- compatible with EXT:cs_seo


### Todos
- currently nothing


### Deprecated
- currently nothing


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
