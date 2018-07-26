<?xml version="1.0" encoding="UTF-8"?>
<!--

XSLT script to format SPARQL Query Results XML Format into Exhibit JSON format

Version 2 : Li Ding (2010-04-19)
* add parameter "tp" to let users to inject some "types" and "properties" section into output exhibit/json file

Version 1 : Li Ding (2009-11-16)

Acknowledgement:
* this script reused code from Jeni Tennison's original XSLT for handling Google visualization, http://www.jenitennison.com/visualisation/data/SRXtoGoogleVisData.xsl


MIT License

Copyright (c) 2009

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
-->

<xsl:stylesheet version="1.0"
  xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#"
  xmlns:sparql="http://www.w3.org/2005/sparql-results#">

<xsl:strip-space elements="*" />
<xsl:preserve-space elements="sparql:literal" />
<xsl:output method="text" />

<xsl:param name="tp" />
<!--
tp: a string embedding user contributed "types" and "properties" declaration.
    Note: This translation only generate "items" part
   {
<FROM HERE>
       types: {
           "Person": {
               pluralLabel:  "People"
           }
       },
       properties: {
           "age": {
               valueType:    "number"
           },
           "parentOf": {
               label:        "parent of",
               reverseLabel: "child of",
               valueType:    "item"
           }
       },
<TO HERE>
       items: [
           {   label:        "John Doe",
               type:         "Person",
               parentOf:     "Jane Smith"
           },
           {   label:        "Jane Smith",
               type:         "Person"
           }
       ]
   }

-->

<xsl:variable name="defaultNs">
  <xsl:call-template name="namespace">
    <xsl:with-param name="string" select="/rdf:RDF/rdf:Description[1]/rdf:type[1]/@rdf:resource" />
  </xsl:call-template>
</xsl:variable>

<xsl:template match="sparql:sparql">
  <xsl:text>{</xsl:text>

  <xsl:value-of select="$tp" />

  <xsl:apply-templates select="sparql:results" />

  <xsl:text>}</xsl:text>
</xsl:template>

<xsl:template match="sparql:variable">
  <xsl:variable name="name" select="@name" />
  <xsl:variable name="binding"
    select="(/sparql:sparql/sparql:results/sparql:result/sparql:binding[@name = $name])[1]" />
  <xsl:text>{id:'</xsl:text>
  <xsl:value-of select="@name" />

  <xsl:text>',label:'</xsl:text>
  <xsl:value-of select="@name" />

  <xsl:text>',type:'</xsl:text>
  <xsl:choose>
    <xsl:when test="$binding/sparql:uri">string</xsl:when>
    <xsl:otherwise>
      <xsl:variable name="datatype" select="$binding/sparql:literal/@datatype" />
      <xsl:choose>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#decimal'">number</xsl:when>

        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#integer'">number</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#float'">number</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#double'">number</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#int'">number</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#boolean'">boolean</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#date'">date</xsl:when>

        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#dateTime'">datetime</xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#time'">timeofday</xsl:when>
        <xsl:otherwise>
          <xsl:variable name="value" select="$binding/sparql:literal" />
          <xsl:choose>
            <xsl:when test="string(number($value)) != 'NaN'">number</xsl:when>
            <xsl:when test="$value = 'true' or $value = 'false'">boolean</xsl:when>

            <xsl:otherwise>string</xsl:otherwise>
          </xsl:choose>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
  <xsl:text>'}</xsl:text>
</xsl:template>

<xsl:template match="sparql:results">
  <xsl:text>"items":[</xsl:text>
  <xsl:for-each select="sparql:result">
    <xsl:apply-templates select="." />
    <xsl:if test="position() != last()">,</xsl:if>
  </xsl:for-each>
  <xsl:text>]</xsl:text>

</xsl:template>

<xsl:template match="sparql:result">
  <xsl:variable name="result" select="." />
  <xsl:text>{</xsl:text>
  <xsl:for-each select="/sparql:sparql/sparql:head/sparql:variable">
    <xsl:variable name="name" select="@name" />
    <xsl:apply-templates select="$result/sparql:binding[@name = $name]" />
    <xsl:if test="position() != last()">,</xsl:if>

  </xsl:for-each>
  <xsl:text>}</xsl:text>
</xsl:template>

<xsl:template match="sparql:binding">
  <xsl:text>"</xsl:text>
  <xsl:value-of select="@name" />
  <xsl:text>"</xsl:text>
  <xsl:text>:</xsl:text>
  <xsl:choose>
    <xsl:when test="sparql:uri">
      <xsl:text>"</xsl:text>

      <xsl:value-of select="sparql:uri" />
      <xsl:text>"</xsl:text>
    </xsl:when>
    <xsl:otherwise>
      <xsl:variable name="datatype" select="sparql:literal/@datatype" />
      <xsl:choose>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#decimal' or
                        $datatype = 'http://www.w3.org/2001/XMLSchema#integer' or
                        $datatype = 'http://www.w3.org/2001/XMLSchema#float' or
                        $datatype = 'http://www.w3.org/2001/XMLSchema#double' or
                        $datatype = 'http://www.w3.org/2001/XMLSchema#int' or
                        (not($datatype) and string(number(sparql:literal)) != 'NaN')">
          <xsl:value-of select="sparql:literal" />

        </xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#date' or
                        $datatype = 'http://www.w3.org/2001/XMLSchema#dateTime'">
          <xsl:text>new Date(</xsl:text>
          <xsl:value-of select="substring(sparql:literal, 1, 4)" />
          <xsl:text>,</xsl:text>
          <xsl:value-of select="substring(sparql:literal, 6, 2)" />
          <xsl:text>,</xsl:text>

          <xsl:value-of select="substring(sparql:literal, 9, 2)" />
          <xsl:if test="$datatype = 'http://www.w3.org/2001/XMLSchema#dateTime'">
            <xsl:text>,</xsl:text>
            <xsl:value-of select="substring(sparql:literal, 12, 2)" />
            <xsl:text>,</xsl:text>
            <xsl:value-of select="substring(sparql:literal, 15, 2)" />
            <xsl:text>,</xsl:text>

            <xsl:value-of select="substring(sparql:literal, 18, 2)" />
          </xsl:if>
          <xsl:text>)</xsl:text>
        </xsl:when>
        <xsl:when test="$datatype = 'http://www.w3.org/2001/XMLSchema#time'">
          <xsl:text>[</xsl:text>
          <xsl:value-of select="substring(sparql:literal, 1, 2)" />
          <xsl:text>,</xsl:text>

          <xsl:value-of select="substring(sparql:literal, 4, 2)" />
          <xsl:text>,</xsl:text>
          <xsl:value-of select="substring(sparql:literal, 7, 2)" />
          <xsl:text>]</xsl:text>
        </xsl:when>
        <xsl:otherwise>
          <xsl:text>"</xsl:text>

	       <xsl:value-of select="translate(sparql:literal,'&quot;',' ')" />
          <xsl:text>"</xsl:text>
        </xsl:otherwise>
      </xsl:choose>
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>

<xsl:template name="namespace">
  <xsl:param name="string" />
  <xsl:param name="namespace" />
  <xsl:choose>
    <xsl:when test="contains($string, '#')">
      <xsl:value-of select="concat(substring-before($string, '#'), '#')" />
    </xsl:when>
    <xsl:when test="contains($string, '/')">
      <xsl:call-template name="namespace">

        <xsl:with-param name="string" select="substring-after($string, '/')" />
        <xsl:with-param name="namespace"
          select="concat($namespace, substring-before($string, '/'), '/')" />
      </xsl:call-template>
    </xsl:when>
    <xsl:otherwise>
      <xsl:value-of select="$namespace" />
    </xsl:otherwise>
  </xsl:choose>
</xsl:template>


</xsl:stylesheet>
