<!-- CSS goes in the document HEAD or added to your external stylesheet -->
<style type="text/css">
    i {
        font-size: 10px;
    }

    .gede {
        font-size: 30px;
    }

    .doa {
        font-size: 13px;
    }

    table.gridtable {
        /*font-family: verdana,arial,sans-serif;*/
        font-size: 15px;
        color: #333333;
        border-width: 1px;
        border-color: #666666;
        border-collapse: collapse;
    }

    table.gridtable th {
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #666666;
        /*background-color: #dedede;*/
        background-color: #ffffff;
    }

    table.gridtable td {
        border-width: 1px;
        padding: 8px;
        border-style: solid;
        border-color: #666666;
        background-color: #ffffff;
    }
</style>
<!-- Table goes in the document BODY -->
<page style="font-size: 12px; font-family: arial;">
    <table>
        <tr>
            <td width=500><img width='200' src='file://{{logo}}'></td>
            <td width=450 align="center" colspan="2"><h3>BUKTI PENERIMAAN DONASI</h3><I> DONATION RECEIPT</I>
            </td>
        </tr>
        <tr>
            <td height='1'></td>
        </tr>
        <tr>
            <td>
                <table>
                    <tr>
                        <td>Tanggal / <i>date</i></td>
                        <td width='20'></td>
                        <td>{{ddd}}</td>
                    </tr>
                    <tr>
                        <td height='3'></td>
                    </tr>
                    <tr>
                        <td>No Trx / <i>Trx ID</i></td>
                        <td width='20'></td>
                        <td>{{header.id}}</td>
                    </tr>
                    <tr>
                        <td height='3'></td>
                    </tr>
                    <tr>
                        <td>No Urut / <i>batch</i></td>
                        <td width='20'></td>
                        <td>{{header.batch_user}}</td>
                    </tr>
                </table>
            </td>
            <td>
                <table>
                    <tr>
                        <td>Cabang / <i>BRANCH</i></td>
                        <td width='20'></td>
                        <td>{{header.CrCounter.MsBranch.name}} - {{header.CrCounter.name}}</td>
                    </tr>
                    <tr>
                        <td height='3'></td>
                    </tr>
                    <tr>
                        <td>FUNDRAISER</td>
                        <td width='20'></td>
                        <td>{{header.User.HcEmployee.name}}</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <hr>
    <table valign='top'>
        <tr>
            <td style="vertical-align: top;">
                <table>
                    <tr>
                        <td>ID/NAMA</td>
                        <td width='20'></td>
                        <td rowspan=2 valign='middle' width='280'>
                            {{header.CrDonor.public_id}} / {{header.CrDonor.name}}
                        </td>
                    </tr>
                    <tr>
                        <td><i>ID/NAME</i></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td height='10'></td>
                    </tr>
                    <tr>
                        <td>HP/EMAIL</td>
                        <td width='20'></td>
                        <td rowspan=2 valign='middle' width='280'>
                            {{header.CrDonor.hp}} / {{header.CrDonor.email}}
                        </td>
                    </tr>
                    <tr>
                        <td><i>HP/EMAIL</i></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td height='10'></td>
                    </tr>
                    <tr>
                        <td>ALAMAT</td>
                        <td width='20'></td>
                        <td rowspan=2 valign='middle' width='280'>
                            {{header.CrDonor.address}}
                        </td>
                    </tr>
                    <tr>
                        <td><i>ADDRESS</i></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <td height='10'></td>
                    </tr>
                    <tr>
                        <td colspan=3>
                            <table class='gridtable'>

                                <tr>
                                    <td width='280'>
                                        NPWP: {{header.CrDonor.npwp}}
                                    </td>
                                </tr>
                                <tr>
                                    <td width='280'>
                                        Diisi sebagai lampiran SPT Tahunan Pajak Penghasilan, untuk
                                        pengurang Penghasilan Kena Pajak (PKP), sesuai keputusan Dirjen
                                        Pajak No. KEP-163/PJ/2003.
                                    </td>
                                </tr>
                            </table>
                            <table>
                                <tr>
                                    <td align="center">Tanda Tangan Penyetor</td>
                                    <td width='40'></td>
                                    <td align="center">Pengesahan Petugas Amil</td>
                                </tr>
                                <tr>
                                    <td align="center"><i>Authorized signatured</i></td>
                                    <td width='40'></td>
                                    <td align="center"><i>Amil officer's Authorized</i></td>
                                </tr>
                                <tr>
                                    <td height='65'></td>
                                </tr>
                                <tr>
                                    <td align="center">{{header.CrDonor.name}}</td>
                                    <td width='40'></td>
                                    <td align="center">{{header.User.HcEmployee.name}}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
            <td>
                <table class='gridtable'>
                    <tr>
                        <th align='center'>TIPE DONASI <br><i>DONATION TYPE</i></th>
                        <th align='center'>MATA UANG <br><i>CURRENCY</i></th>
                        <th align='center'>RATE<br><i>RATE</i></th>
                        <th align='center'>NILAI <br><i>AMOUNT</i></th>
                        <th align='center'>JUMLAH <br><i>TOTAL</i></th>
                    </tr>
                    {% for dd in detail %}
                        <tr>
                            <td width='180'>
                                {{dd.FaFundType.FaFundCategorySub.FaFundCategory.name}} -
                                {{dd.FaFundType.FaFundCategorySub.name}} - {{dd.FaFundType.name}}
                            </td>
                            <td>{{dd.ms_currency_code}}</td>
                            <td>{{formatNumber(dd.currency_rate)}}</td>
                            <td align='right'>{{formatNumber(dd.currency_amount)}}</td>
                            <td align='right'>{{formatNumber(dd.amount)}}</td>
                        </tr>
                    {% endfor  %}

                    <tr>
                        <td colspan=5>Terbilang / <i>Be Calculated</i> &nbsp;:<b>{{numberToWord(gt)}} RUPIAH</b>
                        </td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td width='550' class='doa'><i class='doa'>
                            Aajarakallaahu fii maa a’thoita, wa baaraka laka abqaita, waja’ala maalaka
                            thohuuraa
                        </i> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; artinya:
                            Semoga Allah memberikan pahala dari harta yang tertunai, memberi keberkahan atas
                            harta yang tersisa, dan semoga Allah menjadikan harta menjadi suci. Aamiin..
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br>

</page>