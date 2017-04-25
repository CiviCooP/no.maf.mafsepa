<div class="crm-block crm-form-block">
  <div class="help-block" id="help">
    {ts}You can add an AvtaleGiro for a contact here. Enter all the details you know, the Avtale Giro will be created as
    NOT active. It will be activated by the start transaction from the bank.{/ts}
  </div>

  {* HEADER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_campaign_id}</div>
      <div class="content">{$form.avtale_giro_campaign_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_max_amount.label}</div>
      <div class="content">{$form.avtale_giro_max_amount.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_amount.label}</div>
      <div class="content">{$form.avtale_giro_amount.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_start_date.label}</div>
      <div class="content">{$form.avtale_giro_start_date.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_frequency_interval.label}</div>
      <div class="content">{$form.avtale_giro_frequency_interval.html}&nbsp;{$form.avtale_giro_frequency_unit_id.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_notification.label}</div>
      <div class="content">{$form.avtale_giro_notification.html}</div>
      <div class="clear"></div>
    </div>
    <div class="crm-section">
      <div class="label">{$form.avtale_giro_account.label}</div>
      <div class="content">{$form.avtale_giro_account.html}</div>
      <div class="clear"></div>
    </div>

  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
