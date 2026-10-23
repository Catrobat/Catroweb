package org.catrobat.web.android;

import android.content.res.Resources;
import android.util.DisplayMetrics;

/** Resources.getSystem() for the browser: only the display metrics, which the stage uses for sizes. */
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
}
