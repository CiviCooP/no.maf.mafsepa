<div class="crm-block crm-content-block crm-contribution-view-form-block">
  <h3>{ts}View AvtaleGiro{/ts}</h3>
  <div class="action-link">
    <div class="crm-submit-buttons">
     <a class="button" href="{$doneUrl}">
       <span><div class="icon ui-icon-close"></div>Done</span>
     </a>
    </div>
  </div>
  <table class="crm-info-panel">
    <tbody>
      <tr>
        <td class="label">{ts}Contact{/ts}</td>
        <td class="content">{$contactDisplayName}</td>
      </tr>
      <tr>
        <td class="label">{ts}Campaign{/ts}</td>
        <td class="content">{$campaignTitle}</td>
      </tr>
      <tr>
        <td class="label">{ts}Amount{/ts}</td>
        <td class="content">{$amount|crmMoney}</td>
      </tr>
      <tr>
        <td class="label">{ts}Max Amount{/ts}</td>
        <td class="content">{$maxAmount|crmMoney}</td>
      </tr>
      <tr>
        <td class="label">{ts}Every{/ts}</td>
        <td class="content">{$frequencyInterval}&nbsp;{$frequencyUnit}</td>
      </tr>
      <tr>
        <td class="label">{ts}Cycle Day{/ts}</td>
        <td class="content">{$cycleDay}</td>
      </tr>
      <tr>
        <td class="label">{ts}Notification?{/ts}</td>
        <td class="content">{$notification}</td>
      </tr>
      <tr>
        <td class="label">{ts}Start Date{/ts}</td>
        <td class="content">{$startDate|crmDate}</td>
      </tr>
      <tr>
        <td class="label">{ts}End Date{/ts}</td>
        <td class="content">{$endDate|crmDate}</td>
      </tr>
      <tr>
        <td class="label">{ts}Active?{/ts}</td>
        <td class="content">{$isActive}</td>
      </tr>
    </tbody>
  </table>
  <div class="action-link">
    <div class="crm-submit-buttons">
     <a class="button" href="{$doneUrl}">
       <span><div class="icon ui-icon-close"></div>Done</span>
     </a>
    </div>
  </div>
</div>