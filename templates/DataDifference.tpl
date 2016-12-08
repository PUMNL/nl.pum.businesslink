<h2>Data Difference when Registering Business Participant</h2>
<p>{ts}Contact ID {/ts}{$data.contact_id}</p>
<table>
  <tr>
    <th>{ts}Field name:{/ts}</th>
    <th>{ts}Old value:{/ts}</th>
    <th>{ts}New value:{/ts}</th>
  </tr>
  {foreach from=$data.values key=fieldName item=fieldValues}
    <tr>
      <td>{$fieldName}</td>
      <td>{$fieldValues.old}</td>
      <td>{$fieldValues.new}</td>
    </tr>
  {/foreach}
</table>