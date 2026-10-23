package org.catrobat.web.compat;

import android.app.Activity;
import android.content.Context;
import android.os.Handler;

/**
 * Stands in for libGDX's AndroidApplication, the superclass of Catroid's StageActivity, which the web backend does
 * not have. On the web StageActivity is never created; the engine only reaches its static members.
 */
public class WebAndroidApplication extends Activity {
	public WebAndroidGraphics graphics;
	public Handler handler;

	public void exit() {
	}

	public Context getContext() {
		return null; // there is no Android context in the browser
	}
}
