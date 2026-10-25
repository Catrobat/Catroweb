package org.catrobat.web.android;

import android.content.ContextWrapper;
import android.hardware.WebSensorManager;

import java.io.File;

/**
 * The application context Catroid asks for on the web: a virtual file system root, a sensor manager without sensors
 * and Catroid's English strings (WebResources).
 */
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
	public android.content.res.Resources getResources() {
		return WebResources.INSTANCE;
	}

	@Override
	public String getString(int id) {
		return WebResources.INSTANCE.getString(id);
	}

	@Override
	public String getString(int id, Object... formatArgs) {
		return WebResources.INSTANCE.getString(id, formatArgs);
	}

	@Override
	public CharSequence getText(int id) {
		return WebResources.INSTANCE.getText(id);
	}

	@Override
	public String getPackageName() {
		return "org.catrobat.catroid";
	}
}
