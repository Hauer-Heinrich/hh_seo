"order" = have to be as parameter ( the highest number wins )

You can use this e. g.:

<html xmlns:f="http://typo3.org/ns/TYPO3/CMS/Fluid/ViewHelpers"
    xmlns:hhs="http://typo3.org/ns/HauerHeinrich/HhSeo/ViewHelpers"
    data-namespace-typo3-fluid="true">

<hhs:MetaTag order="0" data='{
    "headerData":
    {
        "title": "{data.title}",
        "description": "{data.description}",
        "designer": "",
        "theme-color": "",

        "og:title": "{data.og_title}"
    }
}'>
</hhs:MetaTag>

OR like

<hhs:MetaTag order="0">
    {
        "headerData":
        {
            "title": "string",
            "description": "string",
            "designer": "string",
            "theme-color": "RGB",
            "imagetoolbar": "true or false",
            "format-detection": "yes or no",

            "og:title": "fb title"
        },
        "additionalTitleConfig":
        {
            "pageTitleSeparator": "string",
            "pageTitleBefor: "string",
            "pageTitleAfter: "string"
        }
    }
</hhs:MetaTag>

OR like

<hhs:MetaTag order="10" type="headerData" title="My Page Title" description="My Page Description"></hhs:MetaTag>
<hhs:MetaTag order="10" type="additionalTitleConfig" pageTitleSeparator="-" pageTitleBefor="Website Title"></hhs:MetaTag>
