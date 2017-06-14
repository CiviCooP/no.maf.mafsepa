<div class="crm-block crm-form-block">
  {* HEADER *}
  <h2>{ts}OCR Export Defaults{/ts}</h2>
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="top"}
  </div>
  <div class="help-block" id="help">
    {ts}You can enter the settings that will be used when exporting an OCR file here.{/ts}
  </div>
  <div class="crm-section">
    <div class="label">{$form.activity_assignee_id.label}</div>
    <div class="content">{$form.activity_assignee_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.nets_customer_id.label}</div>
    <div class="content">{$form.nets_customer_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.nets_id.label}</div>
    <div class="content">{$form.nets_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.format_code.label}</div>
    <div class="content">{$form.format_code.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.start_service_code.label}</div>
    <div class="content">{$form.start_service_code.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.start_transmission_type.label}</div>
    <div class="content">{$form.start_transmission_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.start_record_type.label}</div>
    <div class="content">{$form.start_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_service_code.label}</div>
    <div class="content">{$form.end_service_code.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_transmission_type.label}</div>
    <div class="content">{$form.end_transmission_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_record_type.label}</div>
    <div class="content">{$form.end_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.assignment_account.label}</div>
    <div class="content">{$form.assignment_account.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.avtale_giro_service_code.label}</div>
    <div class="content">{$form.avtale_giro_service_code.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.assignment_record_type.label}</div>
    <div class="content">{$form.assignment_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.with_notification_transaction_type.label}</div>
    <div class="content">{$form.with_notification_transaction_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.without_notification_transaction_type.label}</div>
    <div class="content">{$form.without_notification_transaction_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.end_assignment_line_record_type.label}</div>
    <div class="content">{$form.end_assignment_line_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.first_contribution_line_record_type.label}</div>
    <div class="content">{$form.first_contribution_line_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.second_contribution_line_record_type.label}</div>
    <div class="content">{$form.second_contribution_line_record_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.default_external_ref.label}</div>
    <div class="content">{$form.default_external_ref.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.membership_external_ref.label}</div>
    <div class="content">{$form.membership_external_ref.html}</div>
    <div class="clear"></div>
  </div>
  {* FOOTER *}
  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>
</div>
