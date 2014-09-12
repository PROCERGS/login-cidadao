package demo;

import java.io.IOException;
import java.io.PrintWriter;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.apache.oltu.oauth2.client.OAuthClient;
import org.apache.oltu.oauth2.client.URLConnectionClient;
import org.apache.oltu.oauth2.client.request.OAuthBearerClientRequest;
import org.apache.oltu.oauth2.client.request.OAuthClientRequest;
import org.apache.oltu.oauth2.client.response.OAuthAccessTokenResponse;
import org.apache.oltu.oauth2.client.response.OAuthAuthzResponse;
import org.apache.oltu.oauth2.client.response.OAuthJSONAccessTokenResponse;
import org.apache.oltu.oauth2.client.response.OAuthResourceResponse;
import org.apache.oltu.oauth2.common.exception.OAuthProblemException;
import org.apache.oltu.oauth2.common.exception.OAuthSystemException;
import org.apache.oltu.oauth2.common.message.types.GrantType;

@WebServlet("/redirect")
public class Redirect extends HttpServlet {
	private static final long serialVersionUID = -637970597432243567L;

	@Override
	public void doGet(HttpServletRequest request, HttpServletResponse response)
			throws ServletException, IOException {
		try {
			OAuthParams oauthParams = Utils.getConfig(request);
			// Create the response wrapper
			OAuthAuthzResponse oar = OAuthAuthzResponse
					.oauthCodeAuthzResponse(request);
			oauthParams.setAuthzCode(oar.getCode());
			getAuthorizationToken(oauthParams);
			getResource(oauthParams);
			Utils.setConfigCookies(response, oauthParams);
			showMeTrue(response, oauthParams);
		} catch (OAuthProblemException e) {
			e.printStackTrace();
		} catch (OAuthSystemException e) {
			e.printStackTrace();
		}
	}

	private void getAuthorizationToken(OAuthParams oauthParams)
			throws OAuthSystemException, OAuthProblemException {
		OAuthClientRequest oRequest = OAuthClientRequest
				.tokenLocation(oauthParams.getTokenEndpoint())
				.setClientId(oauthParams.getClientId())
				.setClientSecret(oauthParams.getClientSecret())
				.setRedirectURI(oauthParams.getRedirectUri())
				.setCode(oauthParams.getAuthzCode())
				.setGrantType(GrantType.AUTHORIZATION_CODE).buildBodyMessage();

		OAuthClient client = new OAuthClient(new URLConnectionClient());

		OAuthAccessTokenResponse oauthResponse = null;
		Class<? extends OAuthAccessTokenResponse> cl = OAuthJSONAccessTokenResponse.class;
		oauthResponse = client.accessToken(oRequest, cl);
		oauthParams.setAccessToken(oauthResponse.getAccessToken());
		oauthParams.setExpiresIn(oauthResponse.getExpiresIn());
		oauthParams.setRefreshToken(oauthResponse.getRefreshToken());
	}

	private void getResource(OAuthParams oauthParams)
			throws OAuthSystemException, OAuthProblemException {		
		OAuthClientRequest request = null;

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
		OAuthResourceResponse resourceResponse = client.resource(request,
				oauthParams.getRequestMethod(), OAuthResourceResponse.class);

		if (resourceResponse.getResponseCode() == 200) {
			oauthParams.setResource(resourceResponse.getBody());
		} else {
			oauthParams.setErrorMessage("Could not access resource: "
					+ resourceResponse.getResponseCode() + " "
					+ resourceResponse.getBody());
		}
	}
	private void showMeTrue(HttpServletResponse response, OAuthParams oauthParams) throws IOException {
		PrintWriter out = response.getWriter();
	    StringBuilder string = new StringBuilder();
	    string.append("<!DOCTYPE HTML PUBLIC '-//W3C//DTD HTML 4.01 Transitional//EN'><html><head><title>OAuth client results</title></head><body>");
	    if (Utils.isEmpty(oauthParams.getErrorMessage())) {
	    	string.append(oauthParams.getResource());
	    } else {
	    	string.append(oauthParams.getErrorMessage());
	    }
	    string.append("</body></html>");
	    out.println(string.toString());
	}

}
