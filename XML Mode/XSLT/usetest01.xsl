<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

    <xsl:template match="/">
        <html>
            <head><title>血庫血液</title></head>
            <body>
                <xsl:for-each select="EMR">
                    <xsl:for-each select="DocumentInfo">
                        <table width="100%" border="0">
                            <TBODY>
                                  <TR>
                                      <TD align="center" width="100%"><B><xsl:value-of select="HospitalName"/></B></TD>
                                  </TR>
                                  <TR>
                                      <TD align="center" width="80%"><B><xsl:value-of select="SheetName"/></B></TD>
                                  </TR>
                                  <TR>
                                      <TD align="right" width="20%"><B>備血單號:<xsl:value-of select="FormSquence"/></B></TD>
                                  </TR>
                                  <TR>
                                      <TD align="right" width="10%"><B>列印日期:<xsl:value-of select="PrinterDate"/></B></TD>
                                  </TR>
                            </TBODY>
                        </table>
                    </xsl:for-each>

                    <xsl:for-each select="Patient">
                        <table width="100%" border="0">
                            <TBODY>
                                    <TR>
                                        <TD width="23%"><B>姓名:<xsl:value-of select="name"/></B></TD>
                                        <TD width="25%">科別:<xsl:value-of select="part"/></TD>
                                        <TD width="22%">身分:<xsl:value-of select="IdType"/></TD>
                                        <TD>備血開單日期:<xsl:value-of select="OrderDate"/></TD>
                                    </TR>
                                    <TR>
                                        <TD width="23%"><B>床號:<xsl:value-of select="bed"/></B></TD>
                                        <TD width="25%">科別:<xsl:value-of select="gender"/></TD>
                                        <TD width="22%">身分:<xsl:value-of select="Diagnosis"/></TD>
                                        <TD>預定用血時間:<xsl:value-of select="ScBloodDateTime"/></TD>
                                    </TR>
                                    <TR>
                                        <TD width="23%"><B>病歷號:<xsl:value-of select="chartNo"/></B></TD>
                                        <TD width="25%">年齡:<xsl:value-of select="age"/></TD>
                                        <TD width="22%">臨床診斷:<xsl:value-of select="ClinicalDiagnosis"/></TD>
                                        <TD><xsl:value-of select="state"/></TD>
                                    </TR>

                                    <TR>
                                        <TD ><B>身分證:<xsl:value-of select="PatientID"/></B></TD>
                                        <TD >醫生: <xsl:value-of select="DoctorName"/></TD>
                                        <TD >輸血原因:<xsl:value-of select="BloodCauses"/></TD>
                                        <TD align="right" style="font-size:30px"><xsl:value-of select="BloodType"/></TD>
                                    </TR>

                                    <TR>
                                        <TD ><B>血庫血型: <xsl:value-of select="BloodType"/></B></TD>
                                        <TD >檢體編號:<xsl:value-of select="SampleNo"/></TD>
                                    </TR>



                                <!--                                <TR>-->
<!--                                    <xsl:for-each select="TableRow1">-->
<!--                                        <TD width="23%"><B>姓名:<xsl:for-each select="name"><xsl:apply-templates/></xsl:for-each></B></TD>-->
<!--                                        <TD width="25%">科別:<xsl:for-each select="part"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD width="22%">身分:<xsl:for-each select="IdType"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD>備血開單日期:<xsl:value-of select="OrderDate"/></TD>-->
<!--                                    </xsl:for-each>-->

<!--                                </TR>-->

<!--                                <TR >-->
<!--                                    <xsl:for-each select="TableRow2">-->
<!--                                        <TD><B>床號:<xsl:for-each select="bed"><xsl:apply-templates/></xsl:for-each></B></TD>-->
<!--                                        <TD >性別:<xsl:for-each select="gender"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD >診別:<xsl:for-each select="Diagnosis"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD>預定用血時間:<xsl:value-of select="ScBloodDateTime"/></TD>-->
<!--                                    </xsl:for-each>-->
<!--                                </TR>-->
<!--                                <TR >-->
<!--                                    <xsl:for-each select="TableRow3">-->
<!--                                        <TD><B>病歷號:<xsl:for-each select="chartNo"><xsl:apply-templates/></xsl:for-each></B></TD>-->
<!--                                        <TD>年齡:<xsl:for-each select="age"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD>臨床診斷:<xsl:for-each select="ClinicalDiagnosis"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD><xsl:value-of select="state"/></TD>-->
<!--                                    </xsl:for-each>-->
<!--                                </TR>-->
<!--                                <TR >-->
<!--                                    <xsl:for-each select="TableRow4">-->
<!--                                        <TD ><B>身分證:<xsl:for-each select="PatientID"><xsl:apply-templates/></xsl:for-each></B></TD>-->
<!--                                        <TD >醫生:<xsl:for-each select="DoctorName"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD >輸血原因:<xsl:for-each select="BloodCauses"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                        <TD  style="font-size:30px"><xsl:for-each select="BloodType"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                    </xsl:for-each>-->

<!--                                </TR>-->
<!--                                <TR>-->
<!--                                    <xsl:for-each select="TableRow5">-->
<!--                                        <TD ><B>血庫血型:<xsl:for-each select="BloodType"><xsl:apply-templates/></xsl:for-each></B></TD>-->
<!--                                        <TD >檢體編號:<xsl:for-each select="SampleNo"><xsl:apply-templates/></xsl:for-each></TD>-->
<!--                                    </xsl:for-each>-->
<!--                                </TR>-->
                            </TBODY>

                        </table>

                    </xsl:for-each>
                    <BR/>
                    <B><p style="text-align:center">備血資料</p></B>

                    <table width="100%" border="1">
                        <TBODY>
                            <TR>
                                <TD align="center" width="20%">血品代碼</TD>
                                <TD align="center" width="60%">血品名稱</TD>
                                <TD align="center" width="20%">備血量</TD>
                            </TR>
                        </TBODY>
                    </table>
                    <table width="100%" border="0">
                        <TBODY>
                            <xsl:for-each select="BloodData">
                                <xsl:for-each select="Data">
                                    <TR>
                                        <TD align="center" width="20%"><xsl:value-of select="BMK_BLDKIND "/></TD>
                                        <TD align="center" width="60%"><xsl:value-of select="EASY_NAME "/></TD>
                                        <TD align="center" width="20%"><xsl:value-of select="BMK_QTY "/></TD>
                                    </TR>
                                </xsl:for-each>
                            </xsl:for-each>
                        </TBODY>
                    </table>
                    <BR/>

                    <table width="100%" border="2">
                        <TBODY>
                            <TR>
                                <TD align="center" colspan="5">紅血球反應</TD>
                                <TD align="center" colspan="4">血清反應</TD>
                            </TR>
                            <TR>
                                <TD align="center">Anti-A</TD>
                                <TD align="center">Anti-B</TD>
                                <TD align="center">Anti-AB</TD>
                                <TD align="center">Anti-D</TD>
                                <TD align="center">血型</TD>
                                <TD align="center">A-Cell</TD>
                                <TD align="center">B-Cell</TD>
                                <TD align="center">交叉試驗</TD>
                                <TD align="center">血型</TD>
                            </TR>
                            <TR>
                                <xsl:for-each select="ReactData">
                                    <TD align="center"><xsl:value-of select="AntiA"/></TD>
                                    <TD align="center"><xsl:value-of select="AntiB"/></TD>
                                    <TD align="center"><xsl:value-of select="AntiAB"/></TD>
                                    <TD align="center"><xsl:value-of select="AntiD"/></TD>
                                    <TD align="center"><xsl:value-of select="BloodType1"/></TD>
                                    <TD align="center"><xsl:value-of select="Acell"/></TD>
                                    <TD align="center"><xsl:value-of select="Bcell"/></TD>
                                    <TD align="center"><xsl:value-of select="Compare"/></TD>
                                    <TD align="center"><xsl:value-of select="BloodType2"/></TD>
                                </xsl:for-each>
                            </TR>
                        </TBODY>
                    </table>
                    <BR/>
                    <table width="100%" border="1">
                        <TBODY>
                            <TR>
                                <TD align="center" >抗體篩檢</TD>
                                <TD align="center" >S I</TD>
                                <TD align="center" >S II</TD>
                                <TD align="center" >S III</TD>
                                <TD align="center" >Auto</TD>
                                <TD align="center" >抗體鑑定</TD>
                                <TD align="center" >血型鑑定</TD>
                            </TR>

                            <xsl:for-each select="AntiData">
                                <TR>
                                    <TD align="center" ><xsl:value-of select="AntiFilter"/></TD>
                                    <TD align="center" ><xsl:value-of select="AntiS1"/></TD>
                                    <TD align="center" ><xsl:value-of select="AntiS2"/></TD>
                                    <TD align="center" ><xsl:value-of select="AntiS3"/></TD>
                                    <TD align="center" ><xsl:value-of select="AntiAuto"/></TD>
                                    <TD align="center" ><xsl:value-of select="AntiEval"/></TD>
                                    <TD align="center" ><xsl:value-of select="BloodEval"/></TD>
                                </TR>
                            </xsl:for-each>
                        </TBODY>
                    </table>
                    <BR/>
                    <table width="100%" border="0">
                        <TBODY>
                            <TR>
                                <xsl:for-each select="OrderType">
                                    <TD width="13%"><xsl:value-of select="N_Normal"/>一般備血</TD>
                                    <TD width="13%"><xsl:value-of select="A_Immediate"/>立即備血</TD>
                                    <TD width="13%"><xsl:value-of select="E_Emergency"/>緊急備血</TD>
                                    <TD width="17%"><xsl:value-of select="Y_VeryEmergency"/>非常緊急備血</TD>
                                    <TD width="23%">醫檢師:<xsl:value-of select="TestDocName"/></TD>
                                </xsl:for-each>
                            </TR>
                        </TBODY>
                    </table>
                    <p>核對者簽章:_____________</p>
                    <p>檢驗備註:</p>


                </xsl:for-each>
            </body>

        </html>



    </xsl:template>

</xsl:stylesheet>