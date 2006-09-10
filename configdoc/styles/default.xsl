<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet
    version     = "1.0"
    xmlns       = "http://www.w3.org/1999/xhtml"
    xmlns:xsl   = "http://www.w3.org/1999/XSL/Transform"
>
    <xsl:output
        method      = "xml"
        encoding    = "UTF-8"
        doctype-public = "-//W3C//DTD XHTML 1.0 Transitional//EN"
        doctype-system = "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"
        indent = "yes"
        media-type = "text/html"
    />
    
    <xsl:template match="/">
        <html lang="en" xml:lang="en">
            <head>
                <title><xsl:value-of select="/configdoc/title" /> Configuration Documentation</title>
                <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
            </head>
            <body>
                <xsl:apply-templates />
            </body>
        </html>
    </xsl:template>
    
    <xsl:template match="title">
        <h1><xsl:value-of select="/configdoc/title" /> Configuration Documentation</h1>
    </xsl:template>
    
    <xsl:template match="namespace">
        <xsl:apply-templates />
        <xsl:if test="count(child::directive)=0">
            <p>No configuration directives defined for this namespace.</p>
        </xsl:if>
    </xsl:template>
    <xsl:template match="namespace/name">
        <h2 id="{../@id}"><xsl:value-of select="text()" /></h2>
    </xsl:template>
    
    <xsl:template match="directive">
        <xsl:apply-templates />
    </xsl:template>
    <xsl:template match="directive/name">
        <h3 id="{../@id}"><xsl:value-of select="text()" /></h3>
    </xsl:template>
    <xsl:template match="directive/type">
        <div class="type"><xsl:value-of select="text()" /></div>
    </xsl:template>
    
</xsl:stylesheet>