<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
	<xsl:output indent="no" omit-xml-declaration="yes" method="text" encoding="UTF-8" media-type="text/x-json"/>
	<xsl:strip-space elements="*"/>
	
	<xsl:template name="escapeQuote">
      <xsl:param name="pText" select="."/>

      <xsl:if test="string-length($pText) >0">
       <xsl:value-of select="substring-before(concat($pText, '&quot;'), '&quot;')"/>

       <xsl:if test="contains($pText, '&quot;')">
        <xsl:text>\"</xsl:text>

        <xsl:call-template name="escapeQuote">
          <xsl:with-param name="pText" select="substring-after($pText, '&quot;')"/>
        </xsl:call-template>
       </xsl:if>
      </xsl:if>
    </xsl:template>



	<xsl:template match="/">
		{"page": [
		<xsl:for-each select="pdf2xml/page">
			{
				"number": <xsl:value-of select="@number" />,
				"position": "<xsl:value-of select="@position" />",
				"t": <xsl:value-of select="@top" />,
				"l": <xsl:value-of select="@left" />,
				"h": <xsl:value-of select="@height" />,
				"w": <xsl:value-of select="@width" />,
				
				"content":[
				<xsl:for-each select="text">
					{
						"t": <xsl:value-of select="@top" />,
						"l": <xsl:value-of select="@left" />,
						"h": <xsl:value-of select="@height" />,
						"w": <xsl:value-of select="@width" />,					
						"text": "<xsl:call-template name="escapeQuote"/>"
					},
				</xsl:for-each>
				{}]
			},
		</xsl:for-each>
		{}]}
	</xsl:template>
</xsl:stylesheet>
