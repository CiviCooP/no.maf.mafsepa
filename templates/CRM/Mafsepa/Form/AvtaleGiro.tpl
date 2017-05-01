<div class="crm-block crm-form-block">
  <div class="help-block" id="help">
    {ts}You can add or edit an AvtaleGiro for a contact here.
      When adding a new one, enter all the details you know, the Avtale Giro will be created as
    NOT active. It will be activated by the start transaction from the bank.{/ts}
  </div>
  {* HEADER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
    <div class="crm-section">
      <div class="label">{$form.campaign_id.label}</div>
      <div class="content">{$form.campaign_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.max_amount.label}</div>
      <div class="content">{$form.max_amount.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.amount.label}</div>
      <div class="content">{$form.amount.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.cycle_day.label}</div>
      <div class="content">{$form.cycle_day.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.start_date.label}</div>
      <div class="content">{$form.start_date.html}</div>
      <div class="clear"></div>
    </div>
    {* end date only if update *}
    {if $action == 2}
      <div class="crm-section">
        <div class="label">{$form.end_date.label}</div>
        <div class="content">{$form.end_date.html}</div>
        <div class="clear"></div>
      </div>
  {/if}
    <div class="crm-section">
      <div class="label">{$form.frequency_interval.label}</div>
      <div class="content">{$form.frequency_interval.html}&nbsp;{$form.frequency_unit_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.notification.label}</div>
      <div class="content">{$form.notification.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.account.label}</div>
      <div class="content">{$form.account.html}</div>
      <div class="clear"></div>
    </div>

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
