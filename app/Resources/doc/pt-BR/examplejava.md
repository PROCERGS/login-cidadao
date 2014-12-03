# Exemplo utilizando Java

## Antes de Começar

Certifique-se de que você tem todos os dados descritos [ aqui ](integration.md#basic_info) antes de seguir o tutorial.

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

É necessário criar um filtro utilizando `javax.servlet.Filter`:
 
``` java
package br.gov.rs.meu.helper;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import javax.servlet.Filter;
import javax.servlet.FilterChain;
import javax.servlet.FilterConfig;
import javax.servlet.ServletException;
import javax.servlet.ServletRequest;
import javax.servlet.ServletResponse;
import javax.servlet.annotation.WebFilter;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import javax.servlet.http.HttpSession;

import org.apache.oltu.oauth2.client.request.OAuthClientRequest;
import org.apache.oltu.oauth2.client.response.OAuthAuthzResponse;
import org.apache.oltu.oauth2.common.message.types.ResponseType;
import org.codehaus.jackson.map.ObjectMapper;
import org.codehaus.jackson.type.TypeReference;

//colocamos como um dos endereços filtrados, o proprio endereço de redireciomento
//assim o proprio filtro vai receber o retorno do gerenciador de identidades
@WebFilter(urlPatterns = { Utils.REDIRECT_URI })
public class AuthFilter implements Filter {
	
	protected List<String> whiteList = new ArrayList<String>();; 
	
	public void doFilter(ServletRequest request, ServletResponse response,
			FilterChain chain) throws IOException, ServletException {
		try {			 
			HttpServletRequest req = (HttpServletRequest) request;
			HttpServletResponse res = (HttpServletResponse) response;
			//primeiro verificamos ser temos alguma sessão ativa, sem criar uma nova sessão
			HttpSession ses = req.getSession(false);
			//pegamos o endereço que o usuario esta tentando acessar
			String reqURI = req.getRequestURI();
			//verificamos se existe na sessão nossa variavel que indica que o usuario esta autenticado			
			if ((ses != null && ses.getAttribute("username") != null) || reqURI.contains("javax.faces.resource") || Utils.inArray(reqURI, whiteList)) {
				chain.doFilter(request, response);
			} else {
			//caso não exista a variável que indique ele esta autenticado
			//verificamos se existe a variável na sessão que indique ele está no processo de autenticação
				if (ses != null && ses.getAttribute("lc.oauthParams") != null) {
					//caso exista o parametro indicando o processo de autenticação
					//recuperamos o parametro
					OAuthParams oauthParams = (OAuthParams) ses.getAttribute("lc.oauthParams");
					//analisamos os parametros do request para ver tem o código de autenticação enviado pelo gerenciador de identidades
					OAuthAuthzResponse oar = OAuthAuthzResponse.oauthCodeAuthzResponse(req);					
					oauthParams.setAuthzCode(oar.getCode());
					//com o código temos obter a Access Token do servidor					
					Utils.getAuthorizationToken(oauthParams);
					// com a Access Token tentamos pegar os dados do usuário
					Utils.getResource(oauthParams);
					//como o retorno do dados do usuário é no formato JSON
					//necesitamos converter esse dado para um objeto, no caso, vamos converter para um objeto do tipo Map
					//utilizamos o jackson para converter JSON para o MAP
					ObjectMapper mapper = new ObjectMapper();					
					Map<String, Object> person = mapper.readValue(
							oauthParams.getResource(),
							new TypeReference<Map<String, Object>>() {
							});
					//apos a convesão preenchemos uma variável da sessão indicando que o usuário esta autenticado
					//nessa variável colocamos a informações do usuário convertidas
					ses.setAttribute("username", person);
					//recuperarmos a variável da sessão que continha o endereço original
					//que o usuário tentava acessar antes do processo de autenticação
					//agora encaminhamos o usuário para esse endereço
					res.sendRedirect((String) ses.getAttribute("lc.origTarget"));
					//removemos o endereço da sessão
					ses.removeAttribute("lc.origTarget");
				} else {
					//pegamos o endereço que o usuario tentou acessar 
					String origTarget = Utils.getFullRequestURL(req);
					//carregamos as configurações para conectar no gerenciador de identidades
					OAuthParams oauthParams = Utils.prepareOAuthParams(req);
					//criamos uma cliente para fazer a requisição da autenticação
					OAuthClientRequest oauthRequest = OAuthClientRequest
							.authorizationLocation(
									oauthParams.getAuthzEndpoint())
							.setClientId(oauthParams.getClientId())
							.setRedirectURI(oauthParams.getRedirectUri())
							.setResponseType(ResponseType.CODE.toString())
							.setScope(oauthParams.getScope())
							.setState(oauthParams.getState())
							.buildQueryMessage();
					//salvamos na sessão as configurações para conectar no gerenciador de identidades
					ses.setAttribute("lc.oauthParams", oauthParams);
					//salvamos na sessao o endereço que o usuário tentou acessar
					ses.setAttribute("lc.origTarget", origTarget);
					//redirecionamos o usuario para o gerenciador de identidades
					res.sendRedirect(oauthRequest.getLocationUri());
				}
			}

		} catch (Throwable t) {
			System.out.println(t.getMessage());
		}

	}

	@Override
	public void destroy() {

	}

	@Override
	public void init(FilterConfig arg0) throws ServletException {
		String whiteList = arg0.getInitParameter("whitelist");
		if (whiteList != null) {
			for (String string : whiteList.split(",")) {
				this.whiteList.add(string);
			}
		}
	}

}
```
[ Voltar ao Índice ](index.md)
