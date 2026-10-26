package org.catrobat.web.catroid;

import java.util.ArrayList;
import java.util.List;

import okhttp3.OkHttpClient;

/**
 * Catroid's WebConnectionHolder for the browser (compat/Rewrite remaps it): same members, no OkHttp client, which
 * needs sockets the browser does not have; the web WebConnection uses fetch() instead.
 */
public class WebConnectionHolder {
	private static final int MAX_CONNECTIONS = 10;
	private List<WebConnection> connections = new ArrayList<>(MAX_CONNECTIONS);
	private OkHttpClient okHttpClient;

	public List<WebConnection> getConnections() {
		return connections;
	}

	public void setConnections(List<WebConnection> connections) {
		this.connections = connections;
	}

	public OkHttpClient getOkHttpClient() {
		return okHttpClient;
	}

	public void setOkHttpClient(OkHttpClient okHttpClient) {
		this.okHttpClient = okHttpClient;
	}

	public void onPause() {
		connections.forEach(WebConnection::cancelCall);
		connections.clear();
	}

	public boolean addConnection(WebConnection connection) {
		return connections.size() < MAX_CONNECTIONS && connections.add(connection);
	}

	public void removeConnection(WebConnection connection) {
		connections.remove(connection);
	}
}
