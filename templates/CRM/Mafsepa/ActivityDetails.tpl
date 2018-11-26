<h2>Detailed information on {$subject}</h2>
{ts}Message{/ts}:&nbsp;{$message}
<table>
  <tr>
    <th>{ts}Field:{/ts}</th>
    <th>{ts}Value:{/ts}</th>
  </tr>
  {foreach from=$details key=label  item=detail}
    <tr>
      <td>{$label}</td>
      <td>{$detail}</td>
    </tr>
  {/foreach}
</table>