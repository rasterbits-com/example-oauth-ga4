Connecting to Google Analytics 4 APIs using OAuth2 authentication.
The libraries that need to be enabled to replace the Google Analytics API are
Google Analytics Data API
Google Analytics Admin API

For more information, you can visit:

v1beta
https://developers.google.com/analytics/devguides/config/admin/v1/rest/v1beta/accounts/list?hl=es-419
Method: accounts.list
Display all the accounts that the issuer can access.
HTTP:
GET https://analyticsadmin.googleapis.com/v1beta/accounts


then 
https://developers.google.com/analytics/devguides/config/admin/v1/rest/v1beta/properties/list?hl=es-419
Display sub-properties within the specified parent account.
Only 'GA4' properties will be shown.
HTTP
GET https://analyticsadmin.googleapis.com/v1beta/properties

