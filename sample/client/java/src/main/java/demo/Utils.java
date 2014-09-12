/**
 *       Copyright 2010 Newcastle University
 *
 *          http://research.ncl.ac.uk/smart/
 *
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package demo;

import javax.servlet.http.Cookie;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

/**
 *
 *
 *
 */
public final class Utils {
	private Utils() {
	}

	public static final String REDIRECT_URI = "/redirect";
	public static final String DISCOVERY_URI = "";

	public static final String REQUEST_TYPE_QUERY = "queryParameter";
	public static final String REQUEST_TYPE_HEADER = "headerField";
	public static final String REQUEST_TYPE_BODY = "bodyParameter";

	public static final String MEU = "generic";
	public static final String MEU_AUTHZ = "https://meu.rs.gov.br/oauth/v2/auth";
	public static final String MEU_TOKEN = "https://meu.rs.gov.br/oauth/v2/token";
	public static final String MEU_RESOURCE = "https://meu.rs.gov.br/api/v1/person";
	public static final String MEU_SUPPORTED_SCOPE = "id username full_name name cpf birthdate email city picture public_profile voter_registration badges country uf city adress adress_number adress_complement rgs";

	public static OAuthParams setConfig(HttpServletRequest request) {
		OAuthParams oauthParams = new OAuthParams();
		oauthParams.setApplication(MEU);
		oauthParams.setAuthzEndpoint(MEU_AUTHZ);
		oauthParams.setRedirectUri(getBaseUrl(request) + REDIRECT_URI);
		oauthParams.setTokenEndpoint(MEU_TOKEN);
		oauthParams.setResourceUrl(MEU_RESOURCE);
		oauthParams.setScope(MEU_SUPPORTED_SCOPE);
		oauthParams.setClientId("");
		oauthParams.setClientSecret("");
		return oauthParams;
	}

	public static OAuthParams getConfig(HttpServletRequest request) {
		OAuthParams oauthParams = new OAuthParams();
		oauthParams.setApplication(MEU);
		oauthParams.setAuthzEndpoint(findCookieValue(request, "authzEndpoint"));
		oauthParams.setRedirectUri(findCookieValue(request, "redirectUri"));
		oauthParams.setTokenEndpoint(findCookieValue(request, "tokenEndpoint"));
		oauthParams.setResourceUrl(MEU_RESOURCE);
		oauthParams.setScope(findCookieValue(request, "scope"));
		oauthParams.setClientId(findCookieValue(request, "clientId"));
		oauthParams.setClientSecret(findCookieValue(request, "clientSecret"));
		oauthParams.setState(findCookieValue(request, "state"));
		oauthParams.setAccessToken(findCookieValue(request, "accessToken"));
		oauthParams.setExpiresIn(findCookieValue(request, "expiresIn"));
		oauthParams.setRefreshToken(findCookieValue(request, "refreshToken"));
		return oauthParams;
	}

	public static String getBaseUrl(HttpServletRequest request) {
		return request.getScheme() + "://" + request.getServerName() + ":"
				+ request.getServerPort() + request.getContextPath();
	}

	public static void setConfigCookies(HttpServletResponse response,
			OAuthParams oauthParams) {
		if (!isEmpty(oauthParams.getClientId())) {
			response.addCookie(new Cookie("clientId", oauthParams.getClientId()));
		}
		if (!isEmpty(oauthParams.getClientSecret())) {
			response.addCookie(new Cookie("clientSecret", oauthParams
					.getClientSecret()));
		}
		if (!isEmpty(oauthParams.getAuthzEndpoint())) {
			response.addCookie(new Cookie("authzEndpoint", oauthParams
					.getAuthzEndpoint()));
		}
		if (!isEmpty(oauthParams.getTokenEndpoint())) {
			response.addCookie(new Cookie("tokenEndpoint", oauthParams
					.getTokenEndpoint()));
		}
		if (!isEmpty(oauthParams.getRedirectUri())) {
			response.addCookie(new Cookie("redirectUri", oauthParams
					.getRedirectUri()));
		}
		if (!isEmpty(oauthParams.getScope())) {
			response.addCookie(new Cookie("scope", oauthParams.getScope()));
		}
		if (!isEmpty(oauthParams.getState())) {
			response.addCookie(new Cookie("state", oauthParams.getState()));
		}
		if (!isEmpty(oauthParams.getApplication())) {
			response.addCookie(new Cookie("app", oauthParams.getApplication()));
		}
		if (!isEmpty(oauthParams.getAuthzCode())) {
			response.addCookie(new Cookie("authzCode", oauthParams
					.getAuthzCode()));
		}
		if (!isEmpty(oauthParams.getAccessToken())) {
			response.addCookie(new Cookie("accessToken", oauthParams
					.getAccessToken()));
		}
		if (oauthParams.getExpiresIn() != null) {
			response.addCookie(new Cookie("expiresIn", oauthParams
					.getExpiresIn().toString()));
		}
		if (!isEmpty(oauthParams.getRefreshToken())) {
			response.addCookie(new Cookie("refreshToken", oauthParams
					.getRefreshToken()));
		}
	}

	public static boolean isEmpty(String value) {
		return value == null || "".equals(value);
	}

	public static String findCookieValue(HttpServletRequest request, String key) {
		Cookie[] cookies = request.getCookies();

		for (Cookie cookie : cookies) {
			if (cookie.getName().equals(key)) {
				return cookie.getValue();
			}
		}
		return "";
	}

}
