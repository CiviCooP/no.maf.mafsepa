<h2>Detailed information on {$subject}</h2>
{ts}Message{/ts}:&nbsp;{$message}
<table>
  <tr>
    <th>{ts}Field:{/ts}</th>
    <th>{ts}Value:{/ts}</th>
  </tr>
  {foreach from=$details item=detail}
    <tr>
      <td>{$detail.label}</td>
      <td>{$detail.value}</td>
    </tr>
  {/foreach}
</table>