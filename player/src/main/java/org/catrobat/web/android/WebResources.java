package org.catrobat.web.android;

import android.content.res.Resources;
import android.util.DisplayMetrics;

import org.teavm.jso.JSBody;

/**
 * Resources.getSystem() and the app context's resources for the browser: the display metrics, which the stage uses
 * for sizes, and Catroid's English strings (true/false in formulas, the default background name, ...).
 */
public final class WebResources extends Resources {
	public static final WebResources INSTANCE = new WebResources();
	private final DisplayMetrics metrics = new DisplayMetrics();

	private WebResources() {
		super(null, null, null);
		metrics.density = 1f;
		metrics.scaledDensity = 1f;
		metrics.densityDpi = 160;
		metrics.xdpi = 160f;
		metrics.ydpi = 160f;
		metrics.widthPixels = 480;
		metrics.heightPixels = 800;
	}

	@Override
	public DisplayMetrics getDisplayMetrics() {
		return metrics;
	}

	@Override
	public String getString(int id) {
		String text = string(id);
		if (null == text) {
			throw new NotFoundException("String resource ID #0x" + Integer.toHexString(id)); // as Android
		}
		return text;
	}

	@Override
	public String getString(int id, Object... formatArgs) {
		return String.format(getString(id), formatArgs);
	}

	@Override
	public CharSequence getText(int id) {
		return getString(id);
	}

	/** From strings.json (scripts/export_strings.py), fetched on the first string a project asks for. */
	@JSBody(params = "id", script = "var s = window.catrobatStrings;"
			+ "if (!s) {"
			+ "  var request = new XMLHttpRequest();"
			+ "  request.open('GET', 'strings.json', false);"
			+ "  request.send();"
			+ "  s = window.catrobatStrings = request.status === 200 ? JSON.parse(request.responseText) : {};"
			+ "}"
			+ "var text = s[id];"
			+ "return text === undefined ? null : text;")
	private static native String string(int id);
}
