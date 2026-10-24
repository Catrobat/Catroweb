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

	@JSBody(params = {"title", "started"}, script = "var overlay = document.createElement('div');"
			+ "overlay.style.cssText = 'position:fixed;inset:0;display:flex;flex-direction:column;align-items:center;"
			+ "justify-content:center;gap:24px;background:rgba(0,0,0,.75);color:#fff;font:600 20px system-ui,sans-serif';"
			+ "var name = document.createElement('div'); name.textContent = title;"
			+ "var button = document.createElement('button'); button.textContent = '\u25B6';"
			+ "button.setAttribute('aria-label', 'Play');"
			+ "button.style.cssText = 'width:96px;height:96px;border-radius:50%;border:0;background:#007f8f;color:#fff;"
			+ "font-size:40px;cursor:pointer';"
			+ "button.onclick = function () { overlay.remove(); started(); };"
			+ "overlay.append(name, button); document.body.append(overlay); button.focus();")
	private static native void show(String title, Sounds.Callback started);
}
