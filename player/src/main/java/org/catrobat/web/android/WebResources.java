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

	private final android.content.res.Configuration configuration = new android.content.res.Configuration();

	/** Left to right, as the defaults of the mockable SDK's Configuration say (Catroid's BrickLayout asks). */
	@Override
	public android.content.res.Configuration getConfiguration() {
		return configuration;
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

	/**
	 * Only the code export (codeview/CodeExport) has plurals: as a marker it completes with the quantity, which share
	 * turns into the viewer's language. The player has none, and gets null as from the mockable SDK.
	 */
	@Override
	public String getQuantityString(int id, int quantity) {
		String marker = plural(id);
		// Not a string concatenation: in its recipe TeaVM takes the end mark for one of StringConcatFactory's own tags.
		return null == marker ? null : new StringBuilder(marker).append('\u001f').append(quantity).append('\u0002').toString();
	}

	@Override
	public String getQuantityString(int id, int quantity, Object... formatArgs) {
		return getQuantityString(id, quantity);
	}

	@Override
	public String[] getStringArray(int id) {
		int length = arrayLength(id);
		if (length < 0) {
			throw new NotFoundException("String array resource ID #0x" + Integer.toHexString(id));
		}
		String[] items = new String[length];
		for (int i = 0; i < length; i++) {
			items[i] = arrayItem(id, i);
		}
		return items;
	}

	@Override
	public CharSequence[] getTextArray(int id) {
		return getStringArray(id);
	}

	@JSBody(params = "id", script = "var p = window.catrobatPlurals; var m = p && p[id]; return m === undefined ? null : m;")
	private static native String plural(int id);

	@JSBody(params = "id", script = "var a = window.catrobatArrays; var m = a && a[id]; return m === undefined ? -1 : m.length;")
	private static native int arrayLength(int id);

	@JSBody(params = {"id", "i"}, script = "return window.catrobatArrays[id][i];")
	private static native String arrayItem(int id, int i);

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
