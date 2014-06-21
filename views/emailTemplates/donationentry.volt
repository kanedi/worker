<style type="text/css">
        table.gridtable {
                /*font-family: verdana,arial,sans-serif;*/
                font-size:11px;
                color:#333333;
                border-width: 1px;
                border-color: #666666;
                border-collapse: collapse;
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
        }
        </style>
<tbody>
<tr>
    <td style="padding:40px 0  0 0;">
        <p style="color:#000;font-size: 16px;line-height:24px;font-family:'HelveticaNeue','Helvetica Neue',Helvetica,Arial,sans-serif;font-weight:normal;">

        <h2 style="font-size: 14px;font-family:'HelveticaNeue','Helvetica Neue',Helvetica,Arial,sans-serif;">Donation Success</h2>

        <p style="font-size: 13px;line-height:24px;font-family:'HelveticaNeue','Helvetica Neue',Helvetica,Arial,sans-serif;">Congratulation, your donation have been submitted
            <br>
            <br>
            <br>Counter Name        : {{ header.CrCounter.name }}
            <br>Operator Name       : {{ header.User.HcEmployee.name }}
            <br>Transaction Date    : {{ date }}
            <br>
            <br>Donor Name : <b>{{ header.CrDonor.name }}</b>
            <br>
            <br><h3>Detail Donation</h3>
            <table class='gridtable'>
              <tr>
                <th><b>Donation Type</b></th>
                <th><b>Currency</b></th>
                <th><b>Currency Rate</b></th>
                <th><b>Amount</b></th>
                <th><b>Total</b></th>
              </tr>
            {% for details in detail %}
              <tr>
                <td>{{ details.FaFundType.name }}</td>
                <td>{{ details.ms_currency_code }}</td>
                <td>{{ details.currency_rate }}</td>
                <td>{{ details.currency_amount }}</td>
                <td>{{ details.amount }}</td>
              </tr>
            {% endfor  %}
            </table>
            <br>--------------------------------------------------------------------------------
            <br>Grand Total : <b>{{ grandtotal }}</b>
            <br>--------------------------------------------------------------------------------
            <br>
            <br>
            Thank you very much for your donation, may Alloh always with you..
            <br>
            Best Regards
            <br>
            <br>
            <br>
            <br>
            <br>
            Dompet Dhuafa Enterprise System            
        </p>
    </td>
</tr>
</tbody>