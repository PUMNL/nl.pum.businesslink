# nl.pum.businesslink

## Functional description

This extension creates activity types, relationship types and groups for the Business Link process.

## API

This extension contains several api's which are described below.

### Businesslink.getvisitdetails

The _Businesslink.getvisitdetails_ api returns the details from a proposed visit.

**Parameters**

    activity_id - required

**Return values**

Returns the following values:

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
    aim_of_visit
    result_of_visit
    thank_you_send

### Businesslink.cancelvisit

The _Businesslink.cancelvisit_ api cancels an existing business programme.

**Parameters**

    activity_id - required

**Return values**

This API does not return anything. On failure it will fail by setting is_error to 1 and it will roll back all data changes.

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

### BusinessParticipant.create
This API will add a business participant to CiviCRM from the data entered on the portal by the customer. 
The create API will be called whenever a customer registers a new participant on the webform.

Functionally the API will:
* check if the contact is known based on the email
> * if the contact exists and some of the data is different, the API will add the activity *Different Data on Registration* to the case listing the data differences
> * if the contact does not exist it will create a new contact with a relation *Employee of* to the customer of the case
* create a case role (relationship on the case) of the type *Business participant is*
* create a case of the type TravelCase for the participant

**Parameters**
All parameters are required

    first_name STRING
    last_name STRING
    passport_first_name STRING
    passport_last_name STRING
    passport_number STRING
    passport_expiry_date DATE
    gender STRING
    birth_date DATE
    nationality_id INT
    email STRING
    job_title STRING
    case_id  INT

**Return values**
This API does not return anything. On failure it will fail by setting is_error to 1 and it will roll back all data changes.
