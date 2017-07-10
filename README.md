# ADAL PHP
This sample contains a PHP libary for accessing Azure Active Directory and a sample app that demonstrates the functionality of that library as well as the new Microsoft School Data Sync API.

## Usage
Construct an HttpClient object:
```
$httpclient = new \microsoft\aadphp\HttpClient;
```
Construct a storage object. A storage object needs to implement the \microsoft\aadphp\OIDC\StorageInterface interface. Currently, two sample implementations are available under `microsoft\aadphp\OIDC\StorageProviders` namespace - `SQLite` and `Session`, but you may need to implement this class yourself based on your storage needs or environment.
Initialize `SQLite` storage provider (as shown in `samples` folder):
```
$storage = new \microsoft\aadphp\OIDC\StorageInterface\SQLite(__DIR__.'/storagedb.sqlite');
```
or initialize the `Session` storage provider:
```
$storage = new \microsoft\aadphp\OIDC\StorageInterface\Session();
```
Construct the AzureAD client class using the $httpclient and $storage instances.
```
$client = new \microsoft\aadphp\AAD\Client($httpclient, $storage);
```
Set your client ID, client secret, and redirect URI.
```
$client->set_clientid($clientid);
$client->set_clientsecret($clientsecret);
$client->set_redirecturi($redirecturi);
```

You client is now ready to use.

To initiate an authorization request:
```
$client->authrequest();
```

To handle an authorization request response (this should be in your redirect URI page):
```
list($idtoken, $tokenparams, $stateparams) = $client->handle_auth_response($_REQUEST);
```

To perform a resource-owner credentials request:
```
$returned = $client->rocredsrequest($_POST['username'], $_POST['password']);
$idtoken = \microsoft\aadphp\AAD\IDToken::instance_from_encoded($returned['id_token']);
```

To get user information from an IDToken, call ->claim() on the $idtoken object. This returns OpenID Connect claims:
```
$idtoken->claim('name');
$idtoken->claim('upn');
```
## Authorization Code Flows
  There are 3 authorization code flows one can implement with this library. 

**1. Authorization code flow:**

  This is the common 3 legged OAuth2 flow. This will redirect you to the Azure AD login page if you aren't already logged in to Azure AD and take you through the login process.
  Example:
  To initate a authorization request call the function $client->authrequest(). The client is set to use this method as default.
```
$client->authrequest();
```
  This will perform an authorization request by redirecting resource owner's user agent to authentication endpoint. Your request will be processed and response will be served in the redirect uri you have set earlier in config.php.
  To handle the response call the function $client->handle_auth_response($_REQUEST).
```
list($idtoken, $tokenparams, $stateparams) = $client->handle_auth_response($_REQUEST);
```
  Now, $idtoken has the object of JWT token(you can claim the user information using this token), $tokenparams contain array of token parameters i.e. access_token, refresh_token, $stateparams contain state parameters.

**2. Hybrid code flow:**

  This is the quicker ID Token OAuth2 flow. This will redirect you to the Azure AD login page if you haven't already logged in to Azure AD and take you through the login process.
  Example:
  To initate a hybrid flow request set the client to use hybrid flow then call the function $client->authrequest().It will then return you the list of list of IDToken object and stored state parameters.
```
$client->set_authflow('hybrid');
$client->authrequest();
```
  This will perform an authorization request by redirecting resource owner's user agent to authentication endpoint. Your request will be processed and response will be served in the redirect uri you have set earlier in config.php.
  To handle the response call the function $client->handle_auth_response($_REQUEST).
```
list($idtoken, $stateparams) = $client->handle_id_token($_REQUEST);
```
  Now, $idtoken has the object of JWT token(you can claim the user information using this token), $stateparams contain state parameters.

**3. Resource Owner Password Credentials Grant:**

  This allows you to enter Azure AD login credentials directly in the login form and have authentication happen behind the scenes. You will not be redirected to Azure AD to log in.
  To perform a resource-owner credentials request:
```
$returned = $client->rocredsrequest($_POST['username'], $_POST['password']);
```
  This will perform an authorization request by sending the user credentials to token endpoint. The response($returned) contain array of token parameters i.e. id_token, access_token, refresh_token.

## Samples and Documentation
The `samples` folder contains a sample implementation of the library demonstrating basic authentication in several different ways.

## Community Help and Support
We leverage [Stack Overflow](http://stackoverflow.com/) to work with the community on supporting Azure Active Directory and its SDKs, including this one! We highly recommend you ask your questions on Stack Overflow (we're all on there!) Also browse existing issues to see if someone has had your question before.

We recommend you use the "adal" tag so we can see it! Here is the latest Q&A on Stack Overflow for AAD: [http://stackoverflow.com/questions/tagged/adal](http://stackoverflow.com/questions/tagged/adal)
