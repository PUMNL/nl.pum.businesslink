# nl.pum.businesslink

## Functional description

This extension creates activity types, relationship types and groups for the Business Link process.

## API

This extension contains several api's which are described below.

### Busisnesslink.completevisit

The _Businesslink.completevisit_ api does the following:

* The activity business programme is updated with the relevant information (visit from/to, result of the visist, send thank you note)
* A contact of type organisation is created for the company visited
* A contact of type individual is created for contact person of the company visited
* A relationship Employer is added between the company visisted and the contact person
* A relationship Has Visited is added between the company and the customer
* The company visited and the contact person are added on the activity Business Programme
* The company visisted is added to the group Companies Not Checked
* When a company and contact person already exists on the activity Business Programme those contacts are updated instead of added.

**Parameters**

All parameters are required

    activity_id - not required; if not set a new activity is created
    case_id
    company_name
    company_address
    company_postal_code
    company_city
    company_email
    contact_person_prefix
    contact_person_firstname
    contact_person_lastname
    contact_person_email
    contact_person_phone
    visit_from
    visit_to
    result_of_visit
    thank_you_send - 0 = No; 1 = Yes
    cancelled - 0 = No; 1 = Yes

When the parameter cancelled is set to yes no company contact and no contact persons are created and the activity is set to cancelled.

**Return values**

This API does not return anything. On failure it will fail by setting is_error to 1 and it will roll back all data changes.
