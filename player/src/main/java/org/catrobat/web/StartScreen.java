package org.catrobat.web;

import org.catrobat.web.loader.Sounds;
import org.teavm.interop.Async;
import org.teavm.interop.AsyncCallback;
import org.teavm.jso.JSBody;

/**
 * The project's name and a play button over the stage. Browsers only allow sound after a click on the page itself,
 * so the player waits for it; ?autostart skips it (tests, and Chrome's --autoplay-policy=no-user-gesture-required).
 */
public final class StartScreen {
	private StartScreen() {
	}

	@Async
	public static native Void waitForStart(String title);

	private static void waitForStart(String title, AsyncCallback<Void> started) {
		show(title, () -> {
			Sounds.createContext(); // still inside the click, so the browser lets it play
			started.complete(null);
		});
	}

	/** Removes the start screen, which says "Loading…" until the project's sounds and looks are in. */
	@JSBody(script = "var overlay = document.getElementById('start-screen'); if (overlay) overlay.remove();")
	public static native void close();

	@JSBody(params = {"title", "started"}, script = "var overlay = document.createElement('div'); overlay.id = 'start-screen';"
			+ "overlay.style.cssText = 'position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;"
			+ "justify-content:center;gap:24px;background:rgba(0,0,0,.75);color:#fff;font:600 20px system-ui,sans-serif';"
			+ "var name = document.createElement('div'); name.textContent = title;"
			+ "var button = document.createElement('button'); button.textContent = '\u25B6';"
			+ "button.setAttribute('aria-label', 'Play');"
			+ "button.style.cssText = 'width:96px;height:96px;border-radius:50%;border:0;background:#007f8f;color:#fff;"
			+ "font-size:40px;cursor:pointer';"
			+ "button.onclick = function () {"
			+ "  var loading = document.createElement('div'); loading.textContent = 'Loading\u2026';"
			+ "  loading.setAttribute('role', 'status'); button.replaceWith(loading); started();"
			+ "};"
			+ "overlay.append(name, button); document.body.append(overlay); button.focus();")
	private static native void show(String title, Sounds.Callback started);
}
