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
    var avtaleGiroUrl = CRM.url("civicrm/mafsepa/form/avtalegiro", {reset: 1, action:'add', cid:{/literal}{$contactId}{literal}});
    cj('#sepa_payment_extra_button').attr("href", avtaleGiroUrl);
    cj('#sepa_payment_extra_button span').text('Record Avtale Giro');
  </script>
{/literal}