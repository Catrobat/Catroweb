package org.catrobat.web;

import org.catrobat.web.loader.Sounds;
import org.teavm.interop.Async;
import org.teavm.interop.AsyncCallback;
import org.teavm.jso.JSBody;

/**
 * The start screen of web/start.js, which shows the project's picture and name and what the player is doing while
 * it loads, then a play button. Browsers only allow sound after a click on the page itself, so the player waits for
 * it; ?autostart skips it (tests, and Chrome's --autoplay-policy=no-user-gesture-required) and has no start screen.
 */
public final class StartScreen {
	private StartScreen() {
	}

	@Async
	public static native Void waitForStart(String title, boolean landscape);

	private static void waitForStart(String title, boolean landscape, AsyncCallback<Void> started) {
		ready(title, landscape, () -> {
			Sounds.createContext(); // still inside the click, so the browser lets it play
			started.complete(null);
		});
	}

	/**
	 * Says what the player does next (a phase of start.js: project, preparing, sounds, starting) and returns once the
	 * page shows it, since that step keeps the browser busy.
	 */
	@Async
	public static native Void phase(String name);

	private static void phase(String name, AsyncCallback<Void> shown) {
		showPhase(name, () -> shown.complete(null));
	}

	@JSBody(params = {"name", "shown"}, script = "var s = window.catrobatStart;"
			+ "if (s) s.phase(name, shown); else Promise.resolve().then(function () { shown(); });")
	private static native void showPhase(String name, Sounds.Callback shown);

	@JSBody(params = {"title", "landscape", "started"}, script = "window.catrobatStart.ready(title, landscape, started);")
	private static native void ready(String title, boolean landscape, Sounds.Callback started);

	/** Instead of the play button: the project cannot be played, and why. */
	@JSBody(params = "message", script = "var s = window.catrobatStart; if (s) s.fail(message);")
	public static native void fail(String message);

	/** Removes the start screen once the stage is there. */
	@JSBody(script = "var s = window.catrobatStart; if (s) s.close();")
	public static native void close();
}
