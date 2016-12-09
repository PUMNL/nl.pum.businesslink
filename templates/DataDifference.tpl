<h2>Data Difference when Registering Business Participant {$contact.contact_name} with contact ID {$contact.contact_id}</h2>
<table>
  <tr>
    <th>{ts}Field:{/ts}</th>
    <th>{ts}Old value:{/ts}</th>
    <th>{ts}New value:{/ts}</th>
  </tr>
  {if isset($first_name)}
    <tr>
      <td>{ts}First Name{/ts}</td>
      <td>{$first_name.old}</td>
      <td>{$first_name.new}</td>
    </tr>
  {/if}
  {if isset($last_name)}
    <tr>
      <td>{ts}Last Name{/ts}</td>
      <td>{$last_name.old}</td>
      <td>{$last_name.new}</td>
    </tr>
  {/if}
  {if isset($gender)}
    <tr>
      <td>{ts}Gender{/ts}</td>
      <td>{$gender.old}</td>
      <td>{$gender.new}</td>
    </tr>
  {/if}
  {if isset($nationality)}
    <tr>
      <td>{ts}Nationality{/ts}</td>
      <td>{$nationality.old}</td>
      <td>{$nationality.new}</td>
    </tr>
  {/if}
  {if isset($birth_date)}
    <tr>
      <td>{ts}Date of Birth{/ts}</td>
      <td>{$birth_date.old}</td>
      <td>{$birth_date.new}</td>
    </tr>
  {/if}
  {if isset($job_title)}
    <tr>
      <td>{ts}Job Title{/ts}</td>
      <td>{$job_title.old}</td>
      <td>{$job_title.new}</td>
    </tr>
  {/if}
  {if isset($email)}
    <tr>
      <td>{ts}Email{/ts}</td>
      <td>{$email.old}</td>
      <td>{$email.new}</td>
    </tr>
  {/if}
  {if isset($passport_first_name)}
    <tr>
      <td>{ts}First Name on Passport{/ts}</td>
      <td>{$passport_first_name.old}</td>
      <td>{$passport_first_name.new}</td>
    </tr>
  {/if}
  {if isset($passport_last_name)}
    <tr>
      <td>{ts}Last Name on Passport{/ts}</td>
      <td>{$passport_last_name.old}</td>
      <td>{$passport_last_name.new}</td>
    </tr>
  {/if}
  {if isset($passport_number)}
    <tr>
      <td>{ts}Passport Number{/ts}</td>
      <td>{$passport_number.old}</td>
      <td>{$passport_number.new}</td>
    </tr>
  {/if}
  {if isset($passport_expiry_date)}
    <tr>
      <td>{ts}Passport Expiry Date{/ts}</td>
      <td>{$passport_expiry_date.old}</td>
      <td>{$passport_expiry_date.new}</td>
    </tr>
  {/if}
</table>