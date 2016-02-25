# Example using Java

## Before you begin

Make sure you have all the data described [here](integration.md#basic_info) before following the tutorial.

### Dependencies

To connect the Citizen Login using `Java` is used OAuth client [Apache Oltu] (https://oltu.apache.org/). In this example, the Maven dependencies manager is used. Thus, in the 'project pom.xml` is necessary to add the following dependencies:

```xml
	<properties>
		<oltu.oauth2.version>1.0.0</oltu.oauth2.version>		
	</properties>	
	<dependencies>
		<!-- idm deps -->
		<dependency>
			<artifactId>org.apache.oltu.oauth2.common</artifactId>
			<groupId>org.apache.oltu.oauth2</groupId>
			<version>${oltu.oauth2.version}</version>
		</dependency>
		<dependency>
			<groupId>org.apache.oltu.oauth2</groupId>
			<artifactId>org.apache.oltu.oauth2.client</artifactId>
			<version>${oltu.oauth2.version}</version>
		</dependency>
		<dependency>
			<groupId>org.apache.oltu.oauth2</groupId>
			<artifactId>org.apache.oltu.oauth2.jwt</artifactId>
			<version>${oltu.oauth2.version}</version>
		</dependency>
		<dependency>
			<groupId>org.apache.oltu.oauth2</groupId>
			<artifactId>org.apache.oltu.oauth2.dynamicreg.client</artifactId>
			<version>${oltu.oauth2.version}</version>
		</dependency>
		<dependency>
			<groupId>org.apache.oltu.oauth2</groupId>
			<artifactId>org.apache.oltu.oauth2.dynamicreg.common</artifactId>
			<version>${oltu.oauth2.version}</version>
		</dependency>		
		<!-- json utils -->
		<dependency>
			<groupId>org.codehaus.jackson</groupId>
			<artifactId>jackson-mapper-asl</artifactId>
			<version>1.9.13</version>
		</dependency>		
	</dependencies>
```


## Getting started

### Configuration file

First we create a configuration file `oauth_configuration.properties` regarding the OAuth server we want to use. In this file, specify the type of application that Apache Oltu use, specify the address to authenticate, the address for the Access Token, address for user data, the desired scopes, the public key, the private key and the address to where the manager identities will return the data:

```
//type of application that uses Apache Oltu
application=generic

//address to authenticate
authz_endpoint=https://meu.rs.gov.br/oauth/v2/auth

//address for the Access Token
token_endpoint=https://meu.rs.gov.br/oauth/v2/token

//address for user data
resource_url=https://meu.rs.gov.br/api/v1/person

//desired scopes
scope=

//public key
client_id=

//private key
client_secret=

//address where the manager identities will return data
redirect_uri=
```

### Creating an authentication filter

You must create a filter using `javax.servlet.Filter`. For that use the `src / main / java / br / gov / rs / my / helper / AuthFilter.java` as follows:

``` java
// src/main/java/br/gov/rs/meu/helper/AuthFilter.java

@WebFilter(urlPatterns = { Utils.REDIRECT_URI })
public class AuthFilter implements Filter {
	// ...
}
```

In the annotation `@ WebFilter` add us as one of the addresses filtered the address to where the identities manager returns data

``` java
// src/main/java/br/gov/rs/meu/helper/AuthFilter.java

	public void doFilter(ServletRequest request, ServletResponse response,
			FilterChain chain) throws IOException, ServletException {
		try {
			HttpServletRequest req = (HttpServletRequest) request;
			HttpServletResponse res = (HttpServletResponse) response;
			HttpSession ses = req.getSession(false);
            
			// Keep the address that the user is trying to access
			String reqURI = req.getRequestURI();
            
			// Check if the user is already authenticated in this application
			if ((ses != null && ses.getAttribute("username") != null) || reqURI.contains("javax.faces.resource") || Utils.inArray(reqURI, whiteList)) {
				chain.doFilter(request, response);
			} else {
				// It is not authenticated, but can be authenticating
				if (ses != null && ses.getAttribute("lc.oauthParams") != null) {
					// We obtain the authentication parameters
					OAuthParams oauthParams = (OAuthParams) ses.getAttribute("lc.oauthParams");
					OAuthAuthzResponse oar = OAuthAuthzResponse.oauthCodeAuthzResponse(req);
                    
                    // and check if we received an Authorization Code
					oauthParams.setAuthzCode(oar.getCode());
                    
					// We request an Access Token
					Utils.getAuthorizationToken(oauthParams);
                    
					// and then ask the user data
					Utils.getResource(oauthParams);
                    
					// For simplicity, we store data on a Map
                    // Of course you should deserialize the JSON received 
                    // for a suitable object
					ObjectMapper mapper = new ObjectMapper();					
					Map<String, Object> person = mapper.readValue(
							oauthParams.getResource(),
							new TypeReference<Map<String, Object>>() {
							});
					
                    // At this point, we have already user data
                    // This is where you must persist the user along with their Access and Refresh Tokens.                    
                    // As this is just an example let's just save the session
					ses.setAttribute("username", person);
                    
					// We refer the user to where he originally tried to go
					res.sendRedirect((String) ses.getAttribute("lc.origTarget"));
					ses.removeAttribute("lc.origTarget");
				} else {
                	// The user is not authenticated and also not are authenticating
                    // Then we must refer the User to the identity manager
                    
					// It is a good practice to save the URL that the user tried to submit it after authorization and authentication.
					String origTarget = Utils.getFullRequestURL(req);
                    
					// Prepare the identity manager settings
					OAuthParams oauthParams = Utils.prepareOAuthParams(req);
					OAuthClientRequest oauthRequest = OAuthClientRequest
							.authorizationLocation(
									oauthParams.getAuthzEndpoint())
							.setClientId(oauthParams.getClientId())
							.setRedirectURI(oauthParams.getRedirectUri())
							.setResponseType(ResponseType.CODE.toString())
							.setScope(oauthParams.getScope())
							.setState(oauthParams.getState())
							.buildQueryMessage();
                            
					// Save the session identity manager settings
					ses.setAttribute("lc.oauthParams", oauthParams);
					ses.setAttribute("lc.origTarget", origTarget);
                    
					//Redirect the user to the identity management
					res.sendRedirect(oauthRequest.getLocationUri());
				}
			}

		} catch (Throwable t) {
			System.out.println(t.getMessage());
		}

	}
```

In `src / main / java / br / gov / rs / my / helper / Utils.java` deserves explanation two methods:` `getAuthorizationToken` and getResource`.
The method `getAuthorizationToken` is responsible for getting the Access Token:

```
public static void getAuthorizationToken(OAuthParams oauthParams) throws OAuthSystemException, OAuthProblemException {
	//Prepare the identity manager settings
	OAuthClientRequest oRequest = OAuthClientRequest
			.tokenLocation(oauthParams.getTokenEndpoint())
			.setClientId(oauthParams.getClientId())
			.setClientSecret(oauthParams.getClientSecret())
			.setRedirectURI(oauthParams.getRedirectUri())
			.setCode(oauthParams.getAuthzCode())
			.setGrantType(GrantType.AUTHORIZATION_CODE).buildBodyMessage();

	OAuthClient client = new OAuthClient(new URLConnectionClient());

	OAuthAccessTokenResponse oauthResponse = null;
	//define a generic class to receive the Access Token
	Class<? extends OAuthAccessTokenResponse> cl = OAuthJSONAccessTokenResponse.class;
	//we request the identity manager an Access Token
	oauthResponse = client.accessToken(oRequest, cl);
	//save the Access Token, the Refresh Token and the expiry date
	oauthParams.setAccessToken(oauthResponse.getAccessToken());
	oauthParams.setExpiresIn(oauthResponse.getExpiresIn());
	oauthParams.setRefreshToken(oauthResponse.getRefreshToken());
}
```

The method `getResource` is responsible for obtaining the user data:

```
public static void getResource(OAuthParams oauthParams) throws OAuthSystemException, OAuthProblemException {
	OAuthClientRequest request = null;
	//choose the way we send our Access Token for identity management
	if (Utils.REQUEST_TYPE_QUERY.equals(oauthParams.getRequestType())) {
		request = new OAuthBearerClientRequest(oauthParams.getResourceUrl())
				.setAccessToken(oauthParams.getAccessToken())
				.buildQueryMessage();
	} else if (Utils.REQUEST_TYPE_HEADER.equals(oauthParams
			.getRequestType())) {
		request = new OAuthBearerClientRequest(oauthParams.getResourceUrl())
				.setAccessToken(oauthParams.getAccessToken())
				.buildHeaderMessage();
	} else if (Utils.REQUEST_TYPE_BODY.equals(oauthParams.getRequestType())) {
		request = new OAuthBearerClientRequest(oauthParams.getResourceUrl())
				.setAccessToken(oauthParams.getAccessToken())
				.buildBodyMessage();
	}

	OAuthClient client = new OAuthClient(new URLConnectionClient());
	//we request the identity manager user data
	OAuthResourceResponse resourceResponse = client.resource(request,
			oauthParams.getRequestMethod(), OAuthResourceResponse.class);

	//if you have a positive response from identity manager save the answer
	if (resourceResponse.getResponseCode() == 200) {
		oauthParams.setResource(resourceResponse.getBody());
	} else {
		oauthParams.setErrorMessage("Could not access resource: "
				+ resourceResponse.getResponseCode() + " "
				+ resourceResponse.getBody());
	}
}

```

[Back to index](index.md)
