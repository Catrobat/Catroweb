package org.catrobat.web.catroid;

import com.badlogic.gdx.scenes.scene2d.Action;

import org.catrobat.catroid.content.Scope;
import org.catrobat.catroid.formulaeditor.Formula;
import org.catrobat.catroid.utils.ShowTextUtils;
import org.catrobat.web.android.WebContext;
import org.teavm.jso.JSBody;
import org.teavm.jso.JSObject;

/**
 * The speak and speak-and-wait bricks with the browser's speech synthesis. The app renders the text to a sound file
 * with Google's or Huawei's engine and plays it; neither exists here. "And wait" ends with the speech, or after a
 * time estimated from the text if the browser never reports the end (no voices installed, for one). The app overlaps
 * spoken sound files; the browser queues utterances, so a new one cancels what is still queued, and speech stops and
 * pauses with the stage's sounds (WebCompat's SoundManager redirects).
 */
public final class WebSpeakAction extends Action {
	private final Scope scope;
	private final Formula text;
	private final boolean wait;
	private JSObject utterance;

	public WebSpeakAction(Scope scope, Formula text, boolean wait) {
		this.scope = scope;
		this.text = text;
		this.wait = wait;
	}

	@Override
	public boolean act(float delta) {
		if (null == utterance) {
			String words = null == text ? "" : text.getUserFriendlyString(new ShowTextUtils.AndroidStringProvider(WebContext.INSTANCE), scope);
			utterance = speak(words);
		}
		return !wait || done(utterance);
	}

	@Override
	public void restart() {
		utterance = null;
	}

	// The time-out counts from the start of the speech; until it starts, from now (the start may never be reported).
	// u keeps the utterance: Chrome drops onend for one that is garbage-collected.
	@JSBody(params = "text", script = "var estimate = 1500 + 120 * text.length;"
			+ "var u = {done: false, until: performance.now() + estimate};"
			+ "if (!window.speechSynthesis || !text) { u.done = true; return u; }"
			+ "var speech = u.speech = new SpeechSynthesisUtterance(text);"
			+ "speech.onstart = function () { u.until = performance.now() + estimate; };"
			+ "speech.onend = function () { u.done = true; };"
			// Without voices the browser fails at once; the script then waits the estimate, as for silent speech.
			+ "speech.onerror = function (e) { if (e.error === 'canceled' || e.error === 'interrupted') u.done = true; };"
			+ "window.speechSynthesis.cancel();"
			+ "window.speechSynthesis.speak(speech);"
			+ "return u;")
	private static native JSObject speak(String text);

	@JSBody(script = "if (window.speechSynthesis) window.speechSynthesis.cancel();")
	public static native void stopAll();

	@JSBody(script = "if (window.speechSynthesis) window.speechSynthesis.pause();")
	public static native void pauseAll();

	@JSBody(script = "if (window.speechSynthesis) window.speechSynthesis.resume();")
	public static native void resumeAll();

	@JSBody(params = "u", script = "return u.done || performance.now() > u.until;")
	private static native boolean done(JSObject u);
}
