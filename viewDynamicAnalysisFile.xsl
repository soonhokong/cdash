<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

   <xsl:include href="header.xsl"/>
   <xsl:include href="footer.xsl"/>

   <!-- Local includes -->
   <xsl:include href="local/footer.xsl"/>
   <xsl:include href="local/header.xsl"/>

   <xsl:output method="xml" indent="yes"  doctype-public="-//W3C//DTD XHTML 1.0 Transitional//EN"
   doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd" />
    <xsl:template match="/">
      <html>
       <head>
       <title><xsl:value-of select="cdash/title"/></title>
        <meta name="robots" content="noindex,nofollow" />
         <link rel="StyleSheet" type="text/css">
         <xsl:attribute name="href"><xsl:value-of select="cdash/cssfile"/></xsl:attribute>
         </link>
       <xsl:call-template name="headscripts"/>
       </head>
       <body bgcolor="#ffffff">

<xsl:choose>
<xsl:when test="/cdash/uselocaldirectory=1">
  <xsl:call-template name="header_local"/>
</xsl:when>
<xsl:otherwise>
  <xsl:call-template name="header"/>
</xsl:otherwise>
</xsl:choose>

<br/>

<!-- Main -->
<br/>
<h3>Dynamic analysis started on <xsl:value-of select="cdash/build/buildtime"/></h3>
<table border="0">
<tr><td align="right"><b>Site Name:</b></td><td><xsl:value-of select="cdash/build/site"/></td></tr>
<tr><td align="right"><b>Build Name:</b></td><td><xsl:value-of select="cdash/build/buildname"/></td></tr>
</table>

<a>
<xsl:attribute name="href"><xsl:value-of select="cdash/dynamicanalysis/href"/></xsl:attribute>
<xsl:value-of select="cdash/dynamicanalysis/filename"/></a>

<font>
<xsl:attribute name="color">
  <xsl:choose>
     <xsl:when test="cdash/dynamicanalysis/status='Passed'">
      #00aa00
     </xsl:when>
    <xsl:otherwise>
      #ffcc66
     </xsl:otherwise>
  </xsl:choose>
</xsl:attribute>
<xsl:value-of select="cdash/dynamicanalysis/status"/>
</font>
<pre><xsl:value-of disable-output-escaping="yes" select="cdash/dynamicanalysis/log"/></pre>
 <br/>

<!-- FOOTER -->
<br/>
<xsl:choose>
<xsl:when test="/cdash/uselocaldirectory=1">
  <xsl:call-template name="footer_local"/>
</xsl:when>
<xsl:otherwise>
  <xsl:call-template name="footer"/>
</xsl:otherwise>
</xsl:choose>
        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
