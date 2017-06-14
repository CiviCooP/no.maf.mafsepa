<div class="crm-block crm-form-block">
  {* HEADER *}
  <h2>{ts}Avtale Giro Defaults{/ts}</h2>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="help-block" id="help">
    {ts}You can enter the defaults that will be used when setting up a new AvtaleGiro here.{/ts}
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_campaign_id.label}</div>
    <div class="content">{$form.default_campaign_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_max_amount.label}</div>
    <div class="content">{$form.default_max_amount.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_amount.label}</div>
    <div class="content">{$form.default_amount.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_cycle_day.label}</div>
    <div class="content">{$form.default_cycle_day.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_frequency_interval.label}</div>
    <div class="content">{$form.default_frequency_interval.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_frequency_unit_id.label}</div>
    <div class="content">{$form.default_frequency_unit_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_notification.label}</div>
    <div class="content">{$form.default_notification.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
