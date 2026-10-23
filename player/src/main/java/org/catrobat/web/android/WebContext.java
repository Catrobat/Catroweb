package org.catrobat.web.android;

import android.content.ContextWrapper;

import java.io.File;

/** The application context Catroid asks for on the web: a virtual file system root, nothing else. */
public final class WebContext extends ContextWrapper {
	public static final WebContext INSTANCE = new WebContext();
	private static final File ROOT = new File("/catroid");

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
	public String getPackageName() {
		return "org.catrobat.catroid";
	}
}
