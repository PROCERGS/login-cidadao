package demo;

import java.io.IOException;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.oltu.oauth2.client.request.OAuthClientRequest;
import org.apache.oltu.oauth2.common.exception.OAuthSystemException;
import org.apache.oltu.oauth2.common.message.types.ResponseType;

@WebServlet("/login")
public class Login extends HttpServlet {
	private static final long serialVersionUID = -637970597432243567L;

	@Override
	public void doGet(HttpServletRequest request, HttpServletResponse response)
			throws ServletException, IOException {
		OAuthParams oauthParams = Utils.setConfig(request);
		Utils.setConfigCookies(response, oauthParams);
		try {
			OAuthClientRequest oRequest = OAuthClientRequest
					.authorizationLocation(oauthParams.getAuthzEndpoint())
					.setClientId(oauthParams.getClientId())
					.setRedirectURI(oauthParams.getRedirectUri())
					.setResponseType(ResponseType.CODE.toString())
					.setScope(oauthParams.getScope())
					.setState(oauthParams.getState()).buildQueryMessage();
			response.sendRedirect(oRequest.getLocationUri());
		} catch (OAuthSystemException e) {
			e.printStackTrace();
		}
	}

}
