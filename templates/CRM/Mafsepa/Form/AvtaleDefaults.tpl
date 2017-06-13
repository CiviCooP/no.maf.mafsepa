<div class="crm-block crm-form-block">
  {* HEADER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  {*--------------------------------------------------------------*}
  {* Part with Avtale Giro Defaults                               *}
  {*--------------------------------------------------------------*}
  <h2>{ts}Avtale Giro Defaults{/ts}</h2>
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
  {*--------------------------------------------------------------*}
  {* Part with OCR Export Defaults                                *}
  {*--------------------------------------------------------------*}
  <h2>{ts}OCR Export Defaults{/ts}</h2>
  <div class="help-block" id="help">
    {ts}You can enter the defaults that will be used when exporting an OCR file here.{/ts}
  </div>
  <table class="form-layout-compressed">
    <tr>
      <td class="label">{$form.activity_assignee_id.label}</td>
      <td class="content">{$form.activity_assignee_id.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.nets_customer_id.label}</td>
      <td class="content">{$form.nets_customer_id.html}</td>
      <td class="label">{$form.nets_id.label}</td>
      <td class="content">{$form.nets_id.html}</td>
      <td class="label">{$form.format_code.label}</td>
      <td class="content">{$form.format_code.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.start_service_code.label}</td>
      <td class="content">{$form.start_service_code.html}</td>
      <td class="label">{$form.start_transmission_type.label}</td>
      <td class="content">{$form.start_transmission_type.html}</td>
      <td class="label">{$form.start_record_type.label}</td>
      <td class="content">{$form.start_record_type.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.end_service_code.label}</td>
      <td class="content">{$form.end_service_code.html}</td>
      <td class="label">{$form.end_transmission_type.label}</td>
      <td class="content">{$form.end_transmission_type.html}</td>
      <td class="label">{$form.end_record_type.label}</td>
      <td class="content">{$form.end_record_type.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.assignment_account.label}</td>
      <td class="content">{$form.assignment_account.html}</td>
      <td class="label">{$form.avtale_giro_service_code.label}</td>
      <td class="content">{$form.avtale_giro_service_code.html}</td>
      <td class="label">{$form.assignment_record_type.label}</td>
      <td class="content">{$form.assignment_record_type.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.with_notification_transaction_type.label}</td>
      <td class="content">{$form.with_notification_transaction_type.html}</td>
      <td class="label">{$form.without_notification_transaction_type.label}</td>
      <td class="content">{$form.without_notification_transaction_type.html}</td>
      <td class="label">{$form.end_assignment_line_record_type.label}</td>
      <td class="content">{$form.end_assignment_line_record_type.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.first_contribution_line_record_type.label}</td>
      <td class="content">{$form.first_contribution_line_record_type.html}</td>
      <td class="label">{$form.second_contribution_line_record_type.label}</td>
      <td class="content">{$form.second_contribution_line_record_type.html}</td>
    </tr>
    <tr>
      <td class="label">{$form.default_external_ref.label}</td>
      <td class="content">{$form.default_external_ref.html}</td>
      <td class="label">{$form.membership_external_ref.label}</td>
      <td class="content">{$form.membership_external_ref.html}</td>
    </tr>

  </table>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
