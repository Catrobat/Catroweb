package org.catrobat.web.android;

import android.content.ContextWrapper;
import android.hardware.WebSensorManager;

import java.io.File;

/** The application context Catroid asks for on the web: a virtual file system root and a sensor manager without sensors. */
public final class WebContext extends ContextWrapper {
	public static final WebContext INSTANCE = new WebContext();
	private static final File ROOT = new File("/catroid");
	private static final WebSensorManager SENSORS = new WebSensorManager();

	private WebContext() {
		super(null);
	}

	@Override
	public File getFilesDir() {
		return ROOT;
	}

	@Override
	public File getCacheDir() {
		return new File(ROOT, "cache");
	}

	@Override
	public File getExternalFilesDir(String type) {
		return ROOT;
	}

	@Override
	public Object getSystemService(String name) {
		return SENSOR_SERVICE.equals(name) ? SENSORS : null;
	}

	@Override
	public String getPackageName() {
		return "org.catrobat.catroid";
	}
}
