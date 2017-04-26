{*-------------------------------------------------------+
| CiviCooP - AvtaleGiroButton                            |
| Author: Erik Hommel <erik.hommel@civicoop.org>         |
| https://www.civicoop.org/                              |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}

{literal}
  <script type="text/javascript">
    cj(document).ready(function() {
      // change href and span text for add sepa mandate button
      var avtaleGiroAddUrl = CRM.url("civicrm/mafsepa/form/avtalegiro", {reset: 1, action:'add', cid:{/literal}{$contactId}{literal}});
      cj('#sepa_payment_extra_button').attr("href", avtaleGiroAddUrl);
      cj('#sepa_payment_extra_button span').text('Record Avtale Giro');
      // remove sepa mandate action items
      cj('tbody a').each(function() {
        title = cj(this).attr("title");
        if (title === 'View Recurring Payment' || title === 'Edit Recurring Payment') {
          //cj(this).hide();
        }
      });
      cj('tr').each(function() {
        var trId = cj(this).attr("id");
        if (trId) {
          var rowSplit = trId.split("-");
          console.log('trId is ' + trId);
          console.log(rowSplit);
          if (rowSplit[0] === "contribution_recur" && rowSplit[1]) {
            var rowId = rowSplit[1];
            console.log('contribution recurd id is ' + rowId);
          }
        }
      });
    });
  </script>
{/literal}