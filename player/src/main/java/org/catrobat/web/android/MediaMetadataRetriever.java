package org.catrobat.web.android;

import org.catrobat.web.loader.Sounds;

/** android.media.MediaMetadataRetriever for the browser (compat/Rewrite remaps it): sound durations, from Sounds. */
public class MediaMetadataRetriever {
	public static final int METADATA_KEY_DURATION = 9;

	private String path;

	public void setDataSource(String path) {
		this.path = path;
	}

	/** Milliseconds, as a string like on Android; null for anything else. */
	public String extractMetadata(int key) {
		return METADATA_KEY_DURATION == key ? String.valueOf(Math.round(Sounds.duration(path) * 1000)) : null;
	}

	public void release() {
	}
}
