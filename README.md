# nl.pum.businesslink

## Functional description

This extension creates activity types, relationship types and groups for the Business Link process.

## API

This extension contains several api's which are described below.

### Busisnesslink.submitvisit

The _Businesslink.submitvisit_ api does the following:

* The activity business programme is updated with the relevant information (visit from/to, result of the visist, send thank you note)
* A contact of type organisation is created for the company visited
* A contact of type individual is created for contact person of the company visited
* A relationship Employer is added between the company visisted and the contact person
* A relationship Has Visited is added between the company and the customer
* The company visited and the contact person are added on the activity Business Programme

**Parameters**

All parameters are required

    activity_id
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



