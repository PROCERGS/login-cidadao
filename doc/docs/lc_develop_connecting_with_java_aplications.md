# Exemplo utilizando Java

## Antes de Começar

Certifique-se de que você tem todos os dados descritos [ aqui ](lc_develop_integration.md#basic_info) antes de seguir o tutorial.

### Dependências

Para conectar no Login Cidadão usando `Java` é utilizado o cliente OAuth [Apache Oltu](https://oltu.apache.org/). Nesse exemplo, será utilizado o gerenciador de dependencias Maven. Assim, no `pom.xml` do projeto é necessario adicionar as seguintes dependencias:

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


## Começando

### Arquivo de configuração

Primeiramente criamos um arquivo de configuração `oauth_configuration.properties` referente ao servidor OAuth que desejamos utilizar. Nesse arquivo, especificamos o tipo de aplicação que o Apache Oltu utilizará, especificamos o endereço para fazer a autenticação, o endereço para obter o Access Token, endereço para obter os dados do usuário, os escopos desejados, a chave pública, a chave privada e o endereço para onde o gerenciador de identidades irá retornar os dados :

```
//tipo de aplicação que o Apache Oltu utilizará
application=generic

//endereço para fazer a autenticação
authz_endpoint=https://meu.rs.gov.br/oauth/v2/auth

//endereço para obter a Access Token
token_endpoint=https://meu.rs.gov.br/oauth/v2/token

//endereço para obter os dados do usuário
resource_url=https://meu.rs.gov.br/api/v1/person

//escopos desejados
scope=

//chave pública
client_id=

//chave privada
client_secret=

//endereço para onde o gerenciador de identidades irá retornar dados
redirect_uri=
```

### Criando um filtro de autenticação

É necessário criar um filtro utilizando `javax.servlet.Filter`. Para isso utilizamos o arquivo `src/main/java/br/gov/rs/meu/helper/AuthFilter.java` da seguinte forma:

``` java
// src/main/java/br/gov/rs/meu/helper/AuthFilter.java

@WebFilter(urlPatterns = { Utils.REDIRECT_URI })
public class AuthFilter implements Filter {
	// ...
}
```

Na a anotação `@WebFilter` nos adicionamos como um dos endereços filtrados o endereço para aonde o gerenciador de identidades retornará dados.

``` java
// src/main/java/br/gov/rs/meu/helper/AuthFilter.java

	public void doFilter(ServletRequest request, ServletResponse response,
			FilterChain chain) throws IOException, ServletException {
		try {
			HttpServletRequest req = (HttpServletRequest) request;
			HttpServletResponse res = (HttpServletResponse) response;
			HttpSession ses = req.getSession(false);
            
			// Guardamos o endereço que o usuário está tentando acessar
			String reqURI = req.getRequestURI();
            
			// Verificamos se o usuário já está autenticado nesta aplicação
			if ((ses != null && ses.getAttribute("username") != null) || reqURI.contains("javax.faces.resource") || Utils.inArray(reqURI, whiteList)) {
				chain.doFilter(request, response);
			} else {
				// Ele não está autenticado, mas pode estar em processo de autenticação
				if (ses != null && ses.getAttribute("lc.oauthParams") != null) {
					// Obtemos os parâmetros de autenticação
					OAuthParams oauthParams = (OAuthParams) ses.getAttribute("lc.oauthParams");
					OAuthAuthzResponse oar = OAuthAuthzResponse.oauthCodeAuthzResponse(req);
                    
                    // e verificamos se recebemos um Authorization Code
					oauthParams.setAuthzCode(oar.getCode());
                    
					// Solicitamos um Access Token
					Utils.getAuthorizationToken(oauthParams);
                    
					// e, em seguida, solicitamos os dados do usuário
					Utils.getResource(oauthParams);
                    
					// Para simplificar, armazenamos os dados em um Map
                    // Naturalmente você deveria desserializar o JSON recebido
                    // para um objeto apropriado
					ObjectMapper mapper = new ObjectMapper();					
					Map<String, Object> person = mapper.readValue(
							oauthParams.getResource(),
							new TypeReference<Map<String, Object>>() {
							});
					
                    // Nesse momento já possuimos os dados do usuário
                    // É nesse ponto que você deve persistir o usuário juntamente com
                    // Seus Access e Refresh Tokens
                    // Como isto é apenas um exemplo vamos apenas salvar na sessão
					ses.setAttribute("username", person);
                    
					// Encaminhamos o usuário para onde ele tentou ir originalmente
					res.sendRedirect((String) ses.getAttribute("lc.origTarget"));
					ses.removeAttribute("lc.origTarget");
				} else {
                	// O usuário não está autenticado nem está se autenticando
                    // Então devemos encaminhá-lo para o gerenciador de identidade
                    
					// É uma boa prática salvar a URL que o usuário tentou acessar
                    // para encaminhá-lo depois da autorização e autenticação.
					String origTarget = Utils.getFullRequestURL(req);
                    
					// Preparamos as configurações do gerenciador de identidade
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
                            
					// Salvemos na sessão as configurações do gerenciador de identidade
					ses.setAttribute("lc.oauthParams", oauthParams);
					ses.setAttribute("lc.origTarget", origTarget);
                    
					// Redirecionamos o usuário para o gerenciador de identidades
					res.sendRedirect(oauthRequest.getLocationUri());
				}
			}

		} catch (Throwable t) {
			System.out.println(t.getMessage());
		}

	}
```

No arquivo `src/main/java/br/gov/rs/meu/helper/Utils.java` merece explicação dois metodos: `getAuthorizationToken` e o `getResource`.
O metodo `getAuthorizationToken` é responsável por obter a Access Token:

```
public static void getAuthorizationToken(OAuthParams oauthParams) throws OAuthSystemException, OAuthProblemException {
	//Preparamos as configurações do gerenciador de identidade
	OAuthClientRequest oRequest = OAuthClientRequest
			.tokenLocation(oauthParams.getTokenEndpoint())
			.setClientId(oauthParams.getClientId())
			.setClientSecret(oauthParams.getClientSecret())
			.setRedirectURI(oauthParams.getRedirectUri())
			.setCode(oauthParams.getAuthzCode())
			.setGrantType(GrantType.AUTHORIZATION_CODE).buildBodyMessage();

	OAuthClient client = new OAuthClient(new URLConnectionClient());

	OAuthAccessTokenResponse oauthResponse = null;
	//definimos uma classe genérica para receber a Access Token
	Class<? extends OAuthAccessTokenResponse> cl = OAuthJSONAccessTokenResponse.class;
	//requisitamos ao gerenciador de identidade uma Access Token para
	oauthResponse = client.accessToken(oRequest, cl);
	//salve a Access Token, a Refresh Token e a data de expiração
	oauthParams.setAccessToken(oauthResponse.getAccessToken());
	oauthParams.setExpiresIn(oauthResponse.getExpiresIn());
	oauthParams.setRefreshToken(oauthResponse.getRefreshToken());
}
```

O metodo `getResource` é responsável por obter os dados do usuário:

```
public static void getResource(OAuthParams oauthParams) throws OAuthSystemException, OAuthProblemException {
	OAuthClientRequest request = null;
	//escolhemos a forma que enviamos a nossa Access Token para o gerenciador de identidades
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
	//requisitamos ao gerenciador de identidade os dados do usuário
	OAuthResourceResponse resourceResponse = client.resource(request,
			oauthParams.getRequestMethod(), OAuthResourceResponse.class);

	//caso tenha uma resposta positiva do gerenciador de identidade salve a resposta
	if (resourceResponse.getResponseCode() == 200) {
		oauthParams.setResource(resourceResponse.getBody());
	} else {
		oauthParams.setErrorMessage("Could not access resource: "
				+ resourceResponse.getResponseCode() + " "
				+ resourceResponse.getBody());
	}
}

```

