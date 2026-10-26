package org.catrobat.web.android;

import org.teavm.jso.JSBody;

/**
 * android.os.Vibrator for the browser (compat/Rewrite remaps it), behind Catroid's VibrationManager: navigator.vibrate,
 * which Android's Chrome has and iPhones do not (there it does nothing, as the start screen says).
 */
public class Vibrator {
	@JSBody(script = "return !!navigator.vibrate;")
	private static native boolean canVibrate();

	public boolean hasVibrator() {
		return canVibrate();
	}

	@JSBody(params = "milliseconds", script = "if (navigator.vibrate) navigator.vibrate(milliseconds);")
	private static native void vibrateFor(double milliseconds);

	public void vibrate(long milliseconds) {
		vibrateFor(milliseconds);
	}

	public void cancel() {
		vibrateFor(0);
	}
}
