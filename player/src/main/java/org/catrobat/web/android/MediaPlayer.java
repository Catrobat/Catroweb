package org.catrobat.web.android;

import org.catrobat.web.loader.Sounds;
import org.teavm.jso.JSObject;

/**
 * android.media.MediaPlayer for the browser (compat/Rewrite remaps it), with the calls Catroid's SoundManager makes.
 * Plays the sounds Sounds decoded when the project loaded, so prepare() has nothing left to do.
 */
public class MediaPlayer {
	private String path;
	private JSObject source;
	private boolean playing;
	private double offset;
	private double startedAt;
	private float volume = 1f;

	public void setDataSource(String path) {
		this.path = path;
	}

	public void prepare() {
	}

	public void seekTo(int milliseconds) {
		offset = milliseconds / 1000.0;
	}

	public void start() {
		stopSource();
		source = Sounds.play(path, offset, volume, () -> playing = false); // Sounds.stop silences a stopped one's end
		playing = null != source;
		startedAt = Sounds.now();
	}

	public void pause() {
		if (playing) {
			offset += Sounds.now() - startedAt;
		}
		stopSource();
	}

	public void stop() {
		stopSource();
		offset = 0;
	}

	public void reset() {
		stop();
		path = null;
	}

	public void release() {
		reset();
	}

	public boolean isPlaying() {
		return playing;
	}

	public int getDuration() {
		return (int) Math.round(Sounds.duration(path) * 1000);
	}

	public int getCurrentPosition() {
		return (int) Math.round((playing ? offset + Sounds.now() - startedAt : offset) * 1000);
	}

	public void setVolume(float left, float right) {
		volume = left; // Catroid always passes the same value for both
		Sounds.setVolume(source, volume);
	}

	private void stopSource() {
		playing = false;
		Sounds.stop(source);
		source = null;
	}
}
