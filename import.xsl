<xsl:stylesheet
    xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version='1.0'>

   <xsl:include href="footer.xsl"/>
    <xsl:include href="headerback.xsl"/> 
   
    <xsl:output method="html"/>
    <xsl:template match="/">
      <html>
       <head>
       <title><xsl:value-of select="cdash/title"/></title>
        <meta name="robots" content="noindex,nofollow" />
         <link rel="StyleSheet" type="text/css">
         <xsl:attribute name="href"><xsl:value-of select="cdash/cssfile"/></xsl:attribute>
         </link>
       </head>
       <body bgcolor="#ffffff">
            <xsl:call-template name="headerback"/>
<br/>

<form name="form1" method="post" action="">
Please select the project to import into:
<select name="project">

  <option>
    <xsl:attribute name="value">0</xsl:attribute>
    Choose...
  </option>
  <xsl:for-each select="cdash/project">
  <option>
  <xsl:attribute name="value"><xsl:value-of select="id"/></xsl:attribute>
  <xsl:value-of select="name"/>
  </option>
</xsl:for-each>
</select>
<br/>
Path to the "Sites" directory for this project's dartboard on the server:
<input type="text" name="directory" size="60"/>
<br/>
From:
<input>
<xsl:attribute name="name">monthFrom</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">2</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/monthFrom"/></xsl:attribute>
</input>
<input>
<xsl:attribute name="name">dayFrom</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">2</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/dayFrom"/></xsl:attribute>
</input>
<input>
<xsl:attribute name="name">yearFrom</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">4</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/yearFrom"/></xsl:attribute>
</input>
To:
<input>
<xsl:attribute name="name">monthTo</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">2</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/monthTo"/></xsl:attribute>
</input>
<input>
<xsl:attribute name="name">dayTo</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">2</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/dayTo"/></xsl:attribute>
</input>
<input>
<xsl:attribute name="name">yearTo</xsl:attribute>
<xsl:attribute name="type">text</xsl:attribute>
<xsl:attribute name="size">4</xsl:attribute>
<xsl:attribute name="value"><xsl:value-of select="/cdash/yearTo"/></xsl:attribute>
</input>
<br/>
<p>
You may want to use a small date range when importing from projects with a lot
of dashboard entries.  The importation process may take a long time to complete.
</p>
<br/>
<input type="submit" name="Submit" value="Import"/>
</form>
<br/>
<!-- FOOTER -->
<br/>
<xsl:call-template name="footer"/>
        </body>
      </html>
    </xsl:template>
</xsl:stylesheet>
