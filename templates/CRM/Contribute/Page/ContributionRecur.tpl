{*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.7                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2016                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
*}

{include file="CRM/common/enableDisableApi.tpl"}
{if $recurRows}
  {* get additional data with AvtaleGiro API *}
  {crmAPI var='avtale' entity='AvtaleGiro' action='get' contact_id=$contactId}
  {strip}
    <table class="selector row-highlight">
      <tr class="columnheader">
        <th scope="col">{ts}Campaign{/ts}</th>
        <th scope="col">{ts}Amount{/ts}</th>
        <th scope="col">{ts}Max Amount{/ts}</th>
        <th scope="col">{ts}Notification?{/ts}</th>
        <th scope="col">{ts}Frequency{/ts}</th>
        <th scope="col">{ts}Cycle Day{/ts}</th>
        <th scope="col">{ts}Start Date{/ts}</th>
        <th scope="col">{ts}End Date{/ts}</th>
        <th scope="col">{ts}Active?{/ts}</th>
        <th scope="col">&nbsp;</th>
      </tr>
      {foreach from=$avtale.values item=avtalegiro}
        <tr id="contribution_recur-{$avtalegiro.recur_id}" data-action="cancel" class="crm-entity {cycle values="even-row,odd-row"}{if NOT $avtalegiro.status} disabled{/if}">
          <td>{$avtalegiro.campaign}</td>
          <td>{$avtalegiro.amount|crmMoney:"NOK"}</td>
          <td>{$avtalegiro.max_amount|crmMoney:"NOK"}</td>
          <td>{if $avtalegiro.notification == 1}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
          <td>{$avtalegiro.frequency}</td>
          <td>{$avtalegiro.cycle_day}</td>
          <td>{$avtalegiro.start_date|crmDate}</td>
          <td>{$avtalegiro.end_date|crmDate}</td>
          <td>{if $avtalegiro.status == 1}{ts}Yes{/ts}{else}{ts}No{/ts}{/if}</td>
          <td>
            {if $avtalegiro.status == 1}
              <span><a class="action-item crm-hover-button" href="{crmURL p="civicrm/mafsepa/form/avtalegiro" q="action=update&rid=`$avtalegiro.recur_id`"}" title="Edit AvtaleGiro">Edit</a></span>
            {/if}
            {if $avtalegiro.status == 0}
              <span><a class="action-item crm-hover-button" href="{crmURL p="civicrm/mafsepa/form/avtalegiro" q="action=delete&rid=`$avtalegiro.recur_id`"}" title="Delete AvtaleGiro">Delete</a></span>
            {/if}
          </td>
        </tr>
      {/foreach}
    </table>
  {/strip}
{/if}
