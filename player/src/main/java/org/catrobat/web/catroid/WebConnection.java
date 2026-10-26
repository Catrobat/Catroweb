package org.catrobat.web.catroid;

import org.catrobat.catroid.common.Constants;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSByRef;
import org.teavm.jso.JSFunctor;
import org.teavm.jso.JSObject;

import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Protocol;
import okhttp3.Request;
import okhttp3.Response;
import okhttp3.ResponseBody;

/**
 * Catroid's WebConnection for the browser (compat/Rewrite remaps it): the same request with fetch() instead of OkHttp,
 * answered with the OkHttp Response Catroid's web and look request actions read. The page's CSP decides which hosts
 * it reaches (play: share only); a blocked or failed request ends like one without network, with Catroid's codes.
 */
public class WebConnection {
	private static final int TIMEOUT_MILLIS = 60_000; // Catroid's WebConnectionHolder TIMEOUT_DURATION

	private final String url;
	private WebRequestListener listener;
	private JSObject abort;

	public WebConnection(OkHttpClient okHttpClient, WebRequestListener listener, String url) {
		this.listener = listener;
		this.url = url;
	}

	public WebConnection(WebRequestListener listener, String url) {
		this(null, listener, url);
	}

	/** Answers once, as Catroid's popListener: a late fetch result after a cancel is dropped. */
	private WebRequestListener popListener() {
		WebRequestListener popped = listener;
		listener = null;
		return popped;
	}

	public void sendWebRequest() {
		Request request;
		try {
			request = new Request.Builder().url(url).build();
		} catch (IllegalArgumentException exception) {
			popListener().onRequestError(String.valueOf(Constants.ERROR_BAD_REQUEST));
			return;
		}
		abort = fetch(request.url().toString(), TIMEOUT_MILLIS, (status, type, disposition, body) -> {
			WebRequestListener target = popListener();
			if (null == target) {
				return;
			}
			if (status < 0) {
				target.onRequestError(String.valueOf(-1 == status ? Constants.ERROR_TIMEOUT : Constants.ERROR_SERVER_ERROR));
				return;
			}
			Response built;
			try {
				Response.Builder response = new Response.Builder().request(request).protocol(Protocol.HTTP_1_1).code(status)
						.message("").body(ResponseBody.create(null == type ? null : MediaType.parse(type), bytes(body)));
				if (null != disposition) {
					response.header("Content-Disposition", disposition);
				}
				built = response.build();
			} catch (RuntimeException exception) { // thrown here it would vanish in the browser and the brick wait forever
				target.onRequestError(String.valueOf(Constants.ERROR_SERVER_ERROR));
				return;
			}
			if (built.isSuccessful()) {
				target.onRequestSuccess(built);
			} else {
				target.onRequestError(String.valueOf(status));
			}
		});
	}

	public void cancelCall() {
		WebRequestListener target = popListener();
		if (null != abort) {
			cancel(abort);
		}
		if (null != target) {
			target.onCancelledCall();
		}
	}

	/** Catroid's, which WebAction implements (remapped with this class). */
	public interface WebRequestListener {
		void onRequestSuccess(Response httpResponse);

		void onRequestError(String httpError);

		void onCancelledCall();
	}

	@JSFunctor
	interface Done extends JSObject {
		/** status -1: timed out, -2: failed (network, CORS or CSP); body is an Int8Array. */
		void run(int status, String type, String disposition, JSObject body);
	}

	@JSBody(params = {"url", "timeout", "done"}, script = "var controller = new AbortController(), timedOut = false;"
			+ "var timer = setTimeout(function () { timedOut = true; controller.abort(); }, timeout);"
			+ "fetch(url, {signal: controller.signal, credentials: 'omit'}).then(function (response) {"
			+ "  return response.arrayBuffer().then(function (b) {"
			+ "    clearTimeout(timer);"
			+ "    done(response.status, response.headers.get('Content-Type'), response.headers.get('Content-Disposition'),"
			+ "      new Int8Array(b));"
			+ "  });"
			+ "}).catch(function () { clearTimeout(timer); done(timedOut ? -1 : -2, null, null, null); });"
			+ "return controller;")
	private static native JSObject fetch(String url, int timeout, Done done);

	@JSBody(params = "controller", script = "controller.abort();")
	private static native void cancel(JSObject controller);

	@JSBody(params = "array", script = "return array;")
	@JSByRef
	private static native byte[] bytes(JSObject array);
}
