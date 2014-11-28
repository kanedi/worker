<style type="text/css">
        page{
                background-image: url(donasirekap.jpg);
                width:100%;
                height:100%
        }
               table.gridtable {
                /*font-family: verdana,arial,sans-serif;*/
                font-size:11px;
                color:#333333;
                border-width: 1px;
                border-color: #666666;
                border-collapse: collapse;
                table-layout: fixed;
        }
        table.gridtable th {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #dedede;
        }
        table.gridtable td {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #ffffff;
                text-align: right;
        }
        #judul td {
                border-width: 1px;
                padding: 8px;
                border-style: solid;
                border-color: #666666;
                background-color: #ffffff;
        }
        table.polos {
                table-layout: fixed;
                margin: 0px 0px 0px 35px;
        }
        table.polos tr td{
                font-size:12px;
                overflow: hidden;
                width: 80px;
                height: 15px;
            }
        </style>
<page>
<div style="
background-image: url(donasirekap.jpg);
width:100%;
height:100%;
margin-top:-10px;
">
         <p style="font-size: 13px;line-height:24px;" align='center'>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <table align="left" style="margin: 0px 0px 0px 40px;">
                <tbody>
                <tr>
                <td style="text-align: left; vertical-align: top; width: 250px;">{{ donor_name }}<br> {{ address }}<br>
                </td>
                <td style="width: 200px;"></td>
                <td style="vertical-align: top; height:110px" >
                <table align ="right" style="text-align: left; width: 581px; height: 60px;">
                        <tbody>
                        <tr>
                                <td align='left'>ID Donatur</td>
                                <td>:</td>
                                <td align='left'>{{ donor_id }}</td>
                             </tr>
                             <tr>
                                <td align='left'>Register Area</td>
                                <td>:</td>
                                <td align='left'>{{ branch_name }} - {{ branch_state }}</td>
                             </tr>
                             <tr>
                                <td align='left'>NPWP</td>
                                <td>:</td>
                                <td align='left'>{{ donor_npwp }}</td>
                             </tr>
                             <tr>
                                <td align='left'>Periode Laporan</td>
                                <td>:</td>
                                <td align='left'>{{ month }} {{ year }}</td>
                             </tr>
                        </tbody>
                </table>
                <br>
                </td>
                </tr>
                </tbody>
                </table>
                <br>
                <br>
                <br>
            <table>
            <tr>
                <td style="height:335px">
                <table align='left' class="polos">
                        <tr>
                          <th style="height:30px;width:100px"><b>ID Transaksi</b></th>
                          <th style="width:30px;"><b>TANGGAL</b></th>
                          <th style="width:30px;"><b>TYPE</b></th>
                          <th style="width:80px;"><b>JUMLAH</b></th>
                        </tr>
                      {% for rinci in detail %}
                        <tr>
                          <td>{{ rinci['id'] }}</td>
                          <td>{{ rinci['trx_date'] }}</td>
                          <td>{{ rinci['cat'] }}</td>
                          <td style="width:70px;" align="right">{{ formatNumber(rinci['total']) }}</td>
                        </tr>
                      {% endfor %}
                      <tr>
                       <td colspan="4" align='center' style="font-size:11px;"> {{ kata }}</td>
                      </tr>
                 </table>
                </td>
                <td>
                 <table class="polos" align='left' style="margin: 0px 0px 0px 185x;">
                        <tr>
                           <th style="height:30px;width:100px"><b>ID Transaksi</b></th>
                          <th style="width:30px;"><b>TANGGAL</b></th>
                          <th style="width:30px;"><b>TYPE</b></th>
                          <th style="width:80px;"><b>JUMLAH</b></th>
                        </tr>
                      {% for rinci in detail2 %}
                        <tr>
                          <td>{{ rinci['id'] }}</td>
                          <td>{{ rinci['trx_date'] }}</td>
                          <td>{{ rinci['cat'] }}</td>
                          <td style="width:70px;" align="right">{{ formatNumber(rinci['total']) }}</td>
                        </tr>
                      {% endfor %}
                 </table>
                </td>
            </tr>
            <tr>
             <td colspan="3">
              <table style="margin: 0px 0px 0px 40px;">
                        <tr>
                          <td style="width:140px;" align="left"><b>{{ gtotal['zakat'] }}</b></td>
                          <td style="width:140px;" align="left"><b>{{ gtotal['infak'] }}</b></td>
                          <td style="width:140px;" align="left"><b>{{ gtotal['wakaf'] }}</b></td>
                          <td style="width:140px;" align="left"><b>{{ gtotal['kurban'] }}</b></td>
                        </tr>
                 </table>
             </td>
            </tr>
            <tr>
             <td colspan="3">
              <table style="text-align: left; height: 340px; width: 969px;margin: 100px 0px 0px 40px;"
                cellpadding="2" cellspacing="2">
                <tbody>
                <tr>
                <td style="vertical-align: top; width: 350px;line-height: 10px;font-size:12px"><span
                style="font-weight: bold;">KURBANKU UNTUK-MU SEMATA</span><br>
                <br>
                <div style="text-align: justify;font-size:12px;line-height: 11px;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Ketika Hari Raya Kurban hadir menyapa, kita senantiasa mengingat
                perjuangan orang-orang mulia. Jejak-jejak mereka menjadi simbol
                ketauladanan yang dapat dipetik sebagai pelajaran paling berharga. Kita
                akan selalu mengingat keteguhan Nabi Ibrahim AS dalam menjalankan
                perintah Allah SWT. Tentu kita juga akan mengenang keikhlasan Nabi
                Ismail AS untuk menerima ujian sebagai pertanda cinta kasih kepada
                Allah SWT yang jauh lebih tinggi dibandingkan kecintaannya pada
                hidupnya sendiri. Keihklasannya menjadi contoh kesungguhan dan
                ketulusan sebagai hamba-Nya.<br>
                <div style="text-align: justify;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                22 tahun lalu, Dompet Dhuafa mencoba menorehkan kreasi melalui program
                Tebar Hewan Kurban (THK) sebagai wujud pengorbanan di jalan Allah SWT.
                Penyaluran Hewan Kurban melalui THK diberikan kepada masyarakat di
                daerah-daerah terpencil, terbelakang, rawan gizi dan orang-orang yang</div>
                </div>
                </td>
                <td style="width:15px"></td>
                <td
                style="vertical-align: top; text-align: justify; width: 360px;font-size:11px;line-height: 12px;">tinggal
                di daerah bencana alam dan kerusuhan melalui Mitra Pemberdayaan
                Peternak di daerah setempat. Untuk tahun ini, harga kurban domba /
                kambing standar = Rp. 1.850.000,-/ekor, kambing / domba premium Rp.
                2.450.000,-/ekor, sedangkan Sapi = Rp.10.950.000,-/ekor.<br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Mari berkurban dengan tulus dan
                sepenuh hati di jalan-Nya untuk Masyarakat Indonesia yang membutuhkan
                melalui Program Tebar Hewan Kurban ke rekening atas nama Yayasan Dompet
                Dhuafa di:<br>
                <br>
                <span style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                BNI Syariah : 009.153.8940</span><br style="font-weight: bold;">
                <span style="font-weight: bold;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                BCA : 237.301.4443</span><br style="font-weight: bold;">
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; atas nama
                Yayasan Dompet Dhuafa Republika<br>
                <br>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Untuk informasi lebih
                lengkap, Saudara dapat mengakses situs www.dompetdhuafa.org atau
                menghubungi layan donatur kami melalui telepon di nomor (021) 741 6050.
                Kami akan dengan senang hati melayani Saudara.</td>
                </tr>
                </tbody>
                </table>
             </td>
            </tr>
            </table>
            <br>
        </p>
</div>
</page>