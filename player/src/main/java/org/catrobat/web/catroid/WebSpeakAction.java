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
 * time estimated from the text if the browser never reports the end (no voices installed, for one).
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

	@JSBody(params = "text", script = "var u = {done: false, until: performance.now() + 1500 + 120 * text.length};"
			+ "if (!window.speechSynthesis || !text) { u.done = true; return u; }"
			+ "var speech = new SpeechSynthesisUtterance(text);"
			+ "speech.onend = speech.onerror = function () { u.done = true; };"
			+ "window.speechSynthesis.speak(speech);"
			+ "return u;")
	private static native JSObject speak(String text);

	@JSBody(params = "u", script = "return u.done || performance.now() > u.until;")
	private static native boolean done(JSObject u);
}
