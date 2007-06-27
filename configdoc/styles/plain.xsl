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
        indent = "no"
        media-type = "text/html"
    />
    <xsl:param name="css" select="'styles/plain.css'"/>
    <xsl:param name="title" select="'Configuration Documentation'"/>
    
    <xsl:variable name="typeLookup" select="document('../types.xml')" />
    
    <xsl:template match="/">
        <html lang="en" xml:lang="en">
            <head>
                <title><xsl:value-of select="$title" /> - <xsl:value-of select="/configdoc/title" /></title>
                <meta http-equiv="Content-Type" content="text/html;charset=UTF-8" />
                <link rel="stylesheet" type="text/css" href="{$css}" />
            </head>
            <body>
                <div id="library"><xsl:value-of select="/configdoc/title" /></div>
                <h1><xsl:value-of select="$title" /></h1>
                <h2>Table of Contents</h2>
                <ul id="toc">
                    <xsl:apply-templates mode="toc" />
                </ul>
                <xsl:apply-templates />
            </body>
        </html>
    </xsl:template>
    
    <xsl:template match="title" mode="toc" />
    <xsl:template match="namespace" mode="toc">
        <xsl:if test="count(directive)&gt;0">
            <li>
                <a href="#{@id}"><xsl:value-of select="name" /></a>
                <ul>
                    <xsl:apply-templates select="directive" mode="toc" />
                </ul>
            </li>
        </xsl:if>
    </xsl:template>
    <xsl:template match="directive" mode="toc">
        <li><a href="#{@id}"><xsl:value-of select="name" /></a></li>
    </xsl:template>
    
    <xsl:template match="title" />
    
    <xsl:template match="namespace">
        <xsl:apply-templates />
        <xsl:if test="count(directive)=0">
            <p>No configuration directives defined for this namespace.</p>
        </xsl:if>
    </xsl:template>
    <xsl:template match="namespace/name">
        <h2 id="{../@id}"><xsl:value-of select="." /></h2>
    </xsl:template>
    <xsl:template match="namespace/description">
        <div class="description">
            <xsl:copy-of select="div/node()" />
        </div>
    </xsl:template>
    
    <xsl:template match="directive">
        <xsl:apply-templates />
    </xsl:template>
    <xsl:template match="directive/name">
        <xsl:apply-templates select="../aliases/alias" mode="anchor" />
        <h3 id="{../@id}"><xsl:value-of select="../@id" /></h3>
    </xsl:template>
    <xsl:template match="alias" mode="anchor">
        <a id="{.}"></a>
    </xsl:template>
    
    <!-- Do not pass through -->
    <xsl:template match="alias"></xsl:template>
    
    <xsl:template match="directive/constraints">
        <table class="constraints">
            <xsl:apply-templates />
            <!-- Calculated other values -->
            <xsl:if test="../descriptions/description[@file]">
                <tr>
                    <th>Used by:</th>
                    <td>
                        <xsl:for-each select="../descriptions/description">
                            <xsl:if test="position()&gt;1">, </xsl:if>
                            <xsl:value-of select="@file" />
                        </xsl:for-each>
                    </td>
                </tr>
            </xsl:if>
            <xsl:if test="../aliases/alias">
                <xsl:apply-templates select="../aliases" mode="constraints" />
            </xsl:if>
        </table>
    </xsl:template>
    <xsl:template match="directive/aliases" mode="constraints">
        <th>Aliases:</th>
        <td>
            <xsl:for-each select="alias">
                <xsl:if test="position()&gt;1">, </xsl:if>
                <xsl:value-of select="." />
            </xsl:for-each>
        </td>
    </xsl:template>
    <xsl:template match="directive//description">
        <div class="description">
            <xsl:copy-of select="div/node()" />
        </div>
    </xsl:template>
    
    <xsl:template match="constraints/type">
        <tr>
            <th>Type:</th>
            <td>
                <xsl:variable name="type" select="text()" />
                <xsl:attribute name="class">type type-<xsl:value-of select="$type" /></xsl:attribute>
                <xsl:value-of select="$typeLookup/types/type[@id=$type]/text()" />
                <xsl:if test="@allow-null='yes'">
                    (or null)
                </xsl:if>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="constraints/allowed">
        <tr>
            <th>Allowed values:</th>
            <td>
                <xsl:for-each select="value"><!--
                 --><xsl:if test="position()&gt;1">, </xsl:if>
                    &quot;<xsl:value-of select="." />&quot;<!--
             --></xsl:for-each>
            </td>
        </tr>
    </xsl:template>
    <xsl:template match="constraints/default">
        <tr>
            <th>Default:</th>
            <td><pre><xsl:value-of select="." xml:space="preserve" /></pre></td>
        </tr>
    </xsl:template>
    
</xsl:stylesheet>
